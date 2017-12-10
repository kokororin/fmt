<?php

namespace Guzzle\Tests\Batch;

use Guzzle\Batch\BatchClosureTransfer;

/**
 * @covers Guzzle\Batch\BatchClosureTransfer
 */
class BatchClosureTransferTest extends \Guzzle\Tests\GuzzleTestCase {
	/** @var array|null An array for keeping track of items passed into the transfer closure */
	protected $itemsTransferred;

	/** @var \Guzzle\Batch\BatchClosureTransfer The transfer fixture */
	protected $transferStrategy;

	/**
	 * @expectedException Guzzle\Common\Exception\InvalidArgumentException
	 */
	public function testEnsuresCallableIsCallable() {
		$foo = new BatchClosureTransfer('uh oh!');
	}

	public function testTransferBailsOnEmptyBatch() {
		$batchedItems = [];
		$this->transferStrategy->transfer($batchedItems);

		$this->assertNull($this->itemsTransferred);
	}

	public function testTransfersBatch() {
		$batchedItems = ['foo', 'bar', 'baz'];
		$this->transferStrategy->transfer($batchedItems);

		$this->assertEquals($batchedItems, $this->itemsTransferred);
	}

	protected function setUp() {
		$this->itemsTransferred = null;
		$itemsTransferred = &$this->itemsTransferred;

		$this->transferStrategy = new BatchClosureTransfer(function (array $batch) use (&$itemsTransferred) {
			$itemsTransferred = $batch;
			return;
		});
	}
}
