<?php

namespace Guzzle\Iterator;

/**
 * Pulls out chunks from an inner iterator and yields the chunks as arrays
 */
class ChunkedIterator extends \IteratorIterator {
	/** @var array Current chunk */
	protected $chunk;

	/** @var int Size of each chunk */
	protected $chunkSize;

	/**
	 * @param \Traversable $iterator  Traversable iterator
	 * @param int          $chunkSize Size to make each chunk
	 * @throws \InvalidArgumentException
	 */
	public function __construct(\Traversable $iterator, $chunkSize) {
		$chunkSize = (int) $chunkSize;
		if ($chunkSize < 0) {
			throw new \InvalidArgumentException("The chunk size must be equal or greater than zero; $chunkSize given");
		}

		parent::__construct($iterator);
		$this->chunkSize = $chunkSize;
	}

	public function current() {
		return $this->chunk;
	}

	public function next() {
		$this->chunk = [];
		for ($i = 0; $i < $this->chunkSize && parent::valid(); ++$i) {
			$this->chunk[] = parent::current();
			parent::next();
		}
	}

	public function rewind() {
		parent::rewind();
		$this->next();
	}

	public function valid() {
		return (bool) $this->chunk;
	}
}
