<?php

namespace Guzzle\Tests\Batch;

use Guzzle\Batch\BatchClosureDivisor;

/**
 * @covers Guzzle\Batch\BatchClosureDivisor
 */
class BatchClosureDivisorTest extends \Guzzle\Tests\GuzzleTestCase {
	public function testDividesBatch() {
		$queue = new \SplQueue();
		$queue[] = 'foo';
		$queue[] = 'baz';

		$d = new BatchClosureDivisor(function (\SplQueue $queue, $context) {
			return [
				['foo'],
				['baz'],
			];
		}, 'Bar!');

		$batches = $d->createBatches($queue);
		$this->assertEquals([['foo'], ['baz']], $batches);
	}

	/**
	 * @expectedException Guzzle\Common\Exception\InvalidArgumentException
	 */
	public function testEnsuresCallableIsCallable() {
		$d = new BatchClosureDivisor(new \stdClass());
	}
}
