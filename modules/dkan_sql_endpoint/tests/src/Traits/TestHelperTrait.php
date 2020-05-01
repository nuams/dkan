<?php

namespace Drupal\Tests\dkan_sql_endpoint\Traits;

use MockChain\Options;
use Drupal\Core\Config\ConfigFactory;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_datastore\Service\Factory\Resource as ResourceServiceFactory;
use Drupal\Core\Database\Connection;

/**
 *
 */
trait TestHelperTrait {

  /**
   *
   */
  private function getServices() {
    return (new Options())
      ->add('config.factory', ConfigFactory::class)
      ->add('dkan_datastore.database_table_factory', DatabaseTableFactory::class)
      ->add('dkan_datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('database', Connection::class)
      ->index(0);
  }

}
