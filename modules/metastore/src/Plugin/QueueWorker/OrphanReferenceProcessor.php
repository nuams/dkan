<?php

declare(strict_types = 1);

namespace Drupal\metastore\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Verifies if a dataset property reference is orphaned, then deletes it.
 *
 * @QueueWorker(
 *   id = "orphan_reference_processor",
 *   title = @Translation("Task Worker: Check for orphaned property reference"),
 *   cron = {"time" = 15}
 * )
 *
 * @codeCoverageIgnore
 */
class OrphanReferenceProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\node\NodeStorageInterface $nodeStorage
   *   Node storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, NodeStorageInterface $nodeStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $nodeStorage;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('dkan.common.node_storage')
      );
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function processItem($data) {
    $property_id = $data[0];
    $uuid = $data[1];

    // Search datasets using this uuid for this property id.
    $datasets = $this->nodeStorage
      ->loadByProperties(
              [
                'field_data_type' => 'dataset',
              ]
          );
    foreach ($datasets as $dataset) {
      $data = json_decode($dataset->referenced_metadata);
      $value = $data->{$property_id};
      // Check if uuid is found either directly or in an array.
      $uuid_is_value = $uuid == $value;
      $uuid_found_in_array = is_array($value) && in_array($uuid, $value);
      if ($uuid_is_value || $uuid_found_in_array) {
        // Uuid found in use, abort.
        return;
      }
    }

    // Value reference uuid not found in any dataset, therefore safe to delete.
    $this->deleteReference($property_id, $uuid);
  }

  /**
   * Deletes a reference.
   *
   * @param string $property_id
   *   The property id.
   * @param string $uuid
   *   The uuid.
   */
  protected function deleteReference(string $property_id, string $uuid) {
    $references = $this->nodeStorage
      ->loadByProperties(
              [
                'field_data_type' => $property_id,
                'uuid' => $uuid,
              ]
          );
    if (FALSE !== ($reference = reset($references))) {
      $reference->delete();
    }
  }

}
