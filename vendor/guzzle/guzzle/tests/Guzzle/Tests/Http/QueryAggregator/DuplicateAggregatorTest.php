<?php

namespace Guzzle\Tests\Http;

use Guzzle\Http\QueryAggregator\DuplicateAggregator as Ag;
use Guzzle\Http\QueryString;

class DuplicateAggregatorTest extends \Guzzle\Tests\GuzzleTestCase {
	public function testAggregates() {
		$query = new QueryString();
		$a = new Ag();
		$key = 'facet 1';
		$value = ['size a', 'width b'];
		$result = $a->aggregate($key, $value, $query);
		$this->assertEquals(['facet%201' => ['size%20a', 'width%20b']], $result);
	}

	public function testEncodes() {
		$query = new QueryString();
		$query->useUrlEncoding(false);
		$a = new Ag();
		$key = 'facet 1';
		$value = ['size a', 'width b'];
		$result = $a->aggregate($key, $value, $query);
		$this->assertEquals(['facet 1' => ['size a', 'width b']], $result);
	}
}
