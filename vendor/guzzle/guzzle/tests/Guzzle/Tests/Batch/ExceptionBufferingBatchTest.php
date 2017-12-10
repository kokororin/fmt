<?php

namespace Guzzle\Tests\Batch;

use Guzzle\Batch\Batch;
use Guzzle\Batch\BatchSizeDivisor;
use Guzzle\Batch\ExceptionBufferingBatch;

/**
 * @covers Guzzle\Batch\ExceptionBufferingBatch
 */
class ExceptionBufferingBatchTest extends \Guzzle\Tests\GuzzleTestCase {
	public function testFlushesEntireBatchWhileBufferingErroredBatches() {
		$t = $this->getMockBuilder('Guzzle\Batch\BatchTransferInterface')
			->setMethods(['transfer'])
			->getMock();

		$d = new BatchSizeDivisor(1);
		$batch = new Batch($t, $d);

		$called = 0;
		$t->expects($this->exactly(3))
			->method('transfer')
			->will($this->returnCallback(function ($batch) use (&$called) {
				if (2 === ++$called) {
					throw new \Exception('Foo');
				}
			}));

		$decorator = new ExceptionBufferingBatch($batch);
		$decorator->add('foo')->add('baz')->add('bar');
		$result = $decorator->flush();

		$e = $decorator->getExceptions();
		$this->assertEquals(1, count($e));
		$this->assertEquals(['baz'], $e[0]->getBatch());

		$decorator->clearExceptions();
		$this->assertEquals(0, count($decorator->getExceptions()));

		$this->assertEquals(['foo', 'bar'], $result);
	}
}
