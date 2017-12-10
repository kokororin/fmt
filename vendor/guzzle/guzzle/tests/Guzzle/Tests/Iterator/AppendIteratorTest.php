<?php

namespace Guzzle\Tests\Iterator;

use Guzzle\Iterator\AppendIterator;

/**
 * @covers Guzzle\Iterator\AppendIterator
 */
class AppendIteratorTest extends \PHPUnit_Framework_TestCase {
	public function testTraversesIteratorsInOrder() {
		$a = new \ArrayIterator([
			'a' => 1,
			'b' => 2,
		]);
		$b = new \ArrayIterator([]);
		$c = new \ArrayIterator([
			'c' => 3,
			'd' => 4,
		]);
		$i = new AppendIterator();
		$i->append($a);
		$i->append($b);
		$i->append($c);
		$this->assertEquals([1, 2, 3, 4], iterator_to_array($i, false));
	}
}
