<?php

namespace Drupal\Tests\metastore\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 *
 * @package Drupal\Tests\metastore\Storage
 */
class DataTest extends TestCase {

  public function testGetStorageNode() {

    $data = new Data('dataset', $this->getEtmChain()->getMock());
    $this->assertInstanceOf(NodeStorage::class, $data->getNodeStorage());
  }

  public function testPublishNonDataset() {

    $this->expectExceptionMessage('Publishing currently only implemented for datasets.');
    $nodeData = new Data('foobar', $this->getEtmChain()->getMock());
    $nodeData->publish('1');
  }

  public function testPublishDatasetNotFound() {

    $etmMock = $this->getEtmChain()
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    $this->expectExceptionMessage('Error publishing dataset: 1 not found.');
    $nodeData = new Data('dataset', $etmMock);
    $nodeData->publish('1');
  }

  public function testPublishDraftDataset() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', 'draft')
      ->add(Node::class, 'set')
      ->add(Node::class, 'save')
      ->getMock();

    $nodeData = new Data('dataset', $etmMock);
    $result = $nodeData->publish('1');
    $this->assertEquals(TRUE, $result);
  }

  public function testPublishDatasetAlreadyPublished() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', 'published')
      ->getMock();

    $nodeData = new Data('dataset', $etmMock);
    $result = $nodeData->publish('1');
    $this->assertEquals(FALSE, $result);
  }

  private function getEtmChain() {

    return (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', NodeStorage::class)
      ->add(NodeStorage::class, 'getQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', ['1'])
      ->add(NodeStorage::class, 'getLatestRevisionId', '2')
      ->addd('loadRevision', Node::class);
  }

}
