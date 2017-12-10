<?php

namespace Guzzle\Tests\Batch;

use Guzzle\Batch\Batch;

/**
 * @covers Guzzle\Batch\AbstractBatchDecorator
 */
class AbstractBatchDecoratorTest extends \Guzzle\Tests\GuzzleTestCase {
	public function testProxiesToWrappedObject() {
		$batch = new Batch(
			$this->getMock('Guzzle\Batch\BatchTransferInterface'),
			$this->getMock('Guzzle\Batch\BatchDivisorInterface')
		);

		$decoratorA = $this->getMockBuilder('Guzzle\Batch\AbstractBatchDecorator')
			->setConstructorArgs([$batch])
			->getMockForAbstractClass();

		$decoratorB = $this->getMockBuilder('Guzzle\Batch\AbstractBatchDecorator')
			->setConstructorArgs([$decoratorA])
			->getMockForAbstractClass();

		$decoratorA->add('foo');
		$this->assertFalse($decoratorB->isEmpty());
		$this->assertFalse($batch->isEmpty());
		$this->assertEquals([$decoratorB, $decoratorA], $decoratorB->getDecorators());
		$this->assertEquals([], $decoratorB->flush());
	}
}
