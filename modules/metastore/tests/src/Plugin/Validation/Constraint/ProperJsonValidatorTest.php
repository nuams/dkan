<?php

namespace Drupal\Tests\metastore\Plugin\Validation\Constraint;

use Drupal\metastore\Plugin\Validation\Constraint\ProperJsonValidator;
use Drupal\metastore\RootedJsonDataFactory;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Context\ExecutionContext;
use PHPUnit\Framework\TestCase;

/**
 * Class.
 */
class ProperJsonValidatorTest extends TestCase {

  /**
   * The schema retriever used for testing.
   *
   * @var \Drupal\metastore\SchemaRetriever|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $schemaRetriever;

  /**
   * The RootedJsonDataFactory class used for testing.
   *
   * @var \Drupal\metastore\RootedJsonDataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $rootedJsonDataFactory;

  /**
   * The container used for testing.
   */
  protected $container;

  /**
   * The context used for testing.
   *
   * @var Symfony\Component\Validator\Context\ExecutionContext|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->schemaRetriever = $this->getMockBuilder(SchemaRetriever::class)
      ->disableOriginalConstructor()
      ->setMethods(["retrieve"])
      ->getMock();

    $this->rootedJsonDataFactory = $this->getMockBuilder(RootedJsonDataFactory::class)
      ->disableOriginalConstructor()
      ->setMethods(["getSchemaRetriever"])
      ->getMock();
    $this->rootedJsonDataFactory->method('getSchemaRetriever')->willReturn($this->schemaRetriever);

    $this->container = $this->getMockBuilder(ContainerInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->container->method('get')
      ->with('dkan.metastore.rooted_json_data_wrapper')
      ->willReturn($this->rootedJsonDataFactory);

    $this->context = $this->getMockBuilder(ExecutionContext::class)
      ->setMethods(["addViolation"])
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Public.
   */
  public function testValidationSuccess() {
    $this->schemaRetriever->method('retrieve')->willReturn(
      json_encode(['foo' => 'bar'])
    );

    $validator = ProperJsonValidator::create($this->container);

    $this->context->expects($this->never())->method("addViolation");

    $validator->initialize($this->context);

    $validator->validate([(object) ['value' => "{}"]], new Count(['min' => 1, 'max' => 2]));
  }

  /**
   * Public.
   */
  public function testValidationFailure() {
    $this->schemaRetriever->method('retrieve')->willReturn(
      '{"type":"object","properties": {"number":{ "type":"number"}}}'
    );

    $validator = ProperJsonValidator::create($this->container);

    $this->context->expects($this->once())->method("addViolation");

    $validator->initialize($this->context);

    $validator->validate([(object) ['value' => '{"number":"foo"}']], new Count(['min' => 1, 'max' => 2]));
  }

}
