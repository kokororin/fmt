<?php

namespace Guzzle\Tests\Message;

use Guzzle\Common\Collection;
use Guzzle\Tests\Http\Message\HeaderComparison;

class HeaderComparisonTest extends \Guzzle\Tests\GuzzleTestCase {
	public function filterProvider() {
		return [

			// Headers match
			[[
				'Content-Length' => 'Foo',
			], [
				'Content-Length' => 'Foo',
			], false],

			// Missing header
			[[
				'X-Foo' => 'Bar',
			], [], [
				'- X-Foo' => 'Bar',
			]],

			// Extra headers is present
			[[
				'X-Foo' => 'Bar',
			], [
				'X-Foo' => 'Bar',
				'X-Baz' => 'Jar',
			], [
				'+ X-Baz' => 'Jar',
			]],

			// Header is present but must be absent
			[[
				'!X-Foo' => '*',
			], [
				'X-Foo' => 'Bar',
			], [
				'++ X-Foo' => 'Bar',
			]],

			// Different values
			[[
				'X-Foo' => 'Bar',
			], [
				'X-Foo' => 'Baz',
			], [
				'X-Foo' => 'Baz != Bar',
			]],

			// Wildcard search passes
			[[
				'X-Foo' => '*',
			], [
				'X-Foo' => 'Bar',
			], false],

			// Wildcard search fails
			[[
				'X-Foo' => '*',
			], [], [
				'- X-Foo' => '*',
			]],

			// Ignore extra header if present
			[[
				'X-Foo' => '*',
				'_X-Bar' => '*',
			], [
				'X-Foo' => 'Baz',
				'X-Bar' => 'Jar',
			], false],

			// Ignore extra header if present and is not
			[[
				'X-Foo' => '*',
				'_X-Bar' => '*',
			], [
				'X-Foo' => 'Baz',
			], false],

			// Case insensitive
			[[
				'X-Foo' => '*',
				'_X-Bar' => '*',
			], [
				'x-foo' => 'Baz',
				'x-BAR' => 'baz',
			], false],

			// Case insensitive with collection
			[[
				'X-Foo' => '*',
				'_X-Bar' => '*',
			], new Collection([
				'x-foo' => 'Baz',
				'x-BAR' => 'baz',
			]), false],
		];
	}

	/**
	 * @dataProvider filterProvider
	 */
	public function testComparesHeaders($filters, $headers, $result) {
		$compare = new HeaderComparison();
		$this->assertEquals($result, $compare->compare($filters, $headers));
	}
}
