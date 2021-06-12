<?php

namespace Drupal\common\Controller;

use Drupal\common\Plugin\OpenApiSpec;

/**
 * Helper class to alter OpenAPI spec for only public endpoints.
 */
class AuthCleanupHelper {

  /**
   * Remove auth endpoints and cleanup unused parameters on an OpenAPI spec.
   *
   * @param Drupal\common\Plugin\OpenApiSpec $spec
   *   The original spec.
   *
   * @return \Drupal\common\Plugin\OpenApiSpec
   *   Altered spec.
   */
  public static function makePublicSpec(OpenApiSpec $spec) {
    $filteredSpec = static::removeAuthenticatedEndpoints($spec);
    $filteredSpec = static::cleanUpParameters($filteredSpec);
    $filteredSpec = static::cleanUpSchemas($filteredSpec);
    return $filteredSpec;
  }

  /**
   * Remove API spec endpoints requiring authentication.
   *
   * @param \Drupal\common\Plugin\OpenApiSpec $spec
   *   The original spec.
   *
   * @return \Drupal\common\Plugin\OpenApiSpec
   *   The modified API spec, without authenticated endpoints.
   */
  public static function removeAuthenticatedEndpoints(OpenApiSpec $spec) {
    $specArr = $spec->{"$"};
    foreach ($specArr['paths'] as $path => $methods) {
      static::removeAuthenticatedMethods($methods, $path, $specArr);
    }
    static::cleanUpEndpoints($specArr);
    unset($specArr['components']['securitySchemes']);
    return new OpenApiSpec(json_encode($specArr));
  }

  /**
   * Within a path, remove methods requiring authentication.
   *
   * @param array $methods
   *   Methods for the current path.
   * @param string $path
   *   The path being processed.
   * @param array $specArr
   *   Assoc array with modified openapi spec.
   */
  private static function removeAuthenticatedMethods(array $methods, string $path, array &$specArr) {
    foreach (array_keys($methods) as $method) {
      if (isset($specArr['paths'][$path][$method]['security'])) {
        unset($specArr['paths'][$path][$method]);
      }
    }
  }

  /**
   * Clean up empty endpoints from the spec.
   *
   * @param array $specArr
   *   Assoc array with spec data, loaded by reference.
   */
  private static function cleanUpEndpoints(array &$specArr) {
    foreach ($specArr['paths'] as $path => $methods) {
      if (empty($methods)) {
        unset($specArr['paths'][$path]);
      }
    }
  }

  /**
   * Clean up unused parameters from the spec.
   *
   * @param \Drupal\common\Plugin\OpenApiSpec $spec
   *   The original spec.
   *
   * @return \Drupal\common\Plugin\OpenApiSpec
   *   The OpenAPI spec without .
   */
  public static function cleanUpParameters(OpenApiSpec $spec) {
    $specArr = $spec->{'$'};
    $usedParameters = [];
    foreach ($specArr['paths'] as $pathMethods) {
      static::getParametersFromMethods($pathMethods, $usedParameters);
    }
    foreach (array_keys($specArr["components"]["parameters"]) as $parameter) {
      if (!in_array($parameter, $usedParameters)) {
        unset($specArr["components"]["parameters"][$parameter]);
      }
    }
    return new OpenApiSpec(json_encode($specArr));
  }

  /**
   * Get all used parameters from an element of the spec's paths array.
   *
   * @param array $pathMethods
   *   A single element of the paths array. Keys should be methods (get, post etc).
   * @param array $usedParameters
   *   Array to store the used parameters as they're found.
   */
  private static function getParametersFromMethods(array $pathMethods, array &$usedParameters) {
    foreach ($pathMethods as $method) {
      static::getParametersFromMethod($method, $usedParameters);
    }
  }

  /**
   * Get all used parameters from a method element of a single paths array element.
   *
   * @param array $method
   *   A single method element (post, get etc) of the paths array.
   * @param array $usedParameters
   *   Array to store the used parameters as they're found.
   */
  private static function getParametersFromMethod($method, &$usedParameters) {
    foreach ($method["parameters"] as $parameter) {
      if (isset($parameter['$ref'])) {
        $parts = explode('/', $parameter['$ref']);
        $parameterKey = end($parts);
      }
      if (isset($parameterKey) && !in_array($parameterKey, $usedParameters)) {
        $usedParameters[] = $parameterKey;
      }
    }
  }

  public static function cleanUpSchemas(OpenApiSpec $spec) {
    $specArr = $spec->{"$"};
    foreach (array_keys($specArr['components']['schemas']) as $schemaKey) {
      if (!static::schemaIsUsed($schemaKey, $spec)) {
        unset($specArr['components']['schemas'][$schemaKey]);
      }
    }
    return new OpenApiSpec(json_encode($specArr));
  }

  public static function schemaIsUsed(string $schemaKey, OpenApiSpec $spec) {
    $used = FALSE;
    array_walk_recursive($spec->{'$'}, function ($value, $key) use (&$used, $schemaKey) {
      $pattern = "/^#\/components\/schemas\/$schemaKey([^a-z]|\$)/";
      if ($key == '$ref' && $used === FALSE && preg_match($pattern, $value)) {
        $used = TRUE;
      }
    });
    return $used;
  }
}
