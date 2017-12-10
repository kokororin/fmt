<?php

namespace Guzzle\Tests\Http\Message;

use Guzzle\Http\Message\Header;
use Guzzle\Http\Message\Response;

/**
 * @covers Guzzle\Http\Message\Header
 */
class HeaderTest extends \Guzzle\Tests\GuzzleTestCase {
	protected $test = [
		'zoo' => ['foo', 'Foo'],
		'Zoo' => 'bar',
	];

	public function parseParamsProvider() {
		$res1 = [
			[
				'<http:/.../front.jpeg>' => '',
				'rel' => 'front',
				'type' => 'image/jpeg',
			],
			[
				'<http://.../back.jpeg>' => '',
				'rel' => 'back',
				'type' => 'image/jpeg',
			],
		];

		return [
			[
				'<http:/.../front.jpeg>; rel="front"; type="image/jpeg", <http://.../back.jpeg>; rel=back; type="image/jpeg"',
				$res1,
			],
			[
				'<http:/.../front.jpeg>; rel="front"; type="image/jpeg",<http://.../back.jpeg>; rel=back; type="image/jpeg"',
				$res1,
			],
			[
				'foo="baz"; bar=123, boo, test="123", foobar="foo;bar"',
				[
					['foo' => 'baz', 'bar' => '123'],
					['boo' => ''],
					['test' => '123'],
					['foobar' => 'foo;bar'],
				],
			],
			[
				'<http://.../side.jpeg?test=1>; rel="side"; type="image/jpeg",<http://.../side.jpeg?test=2>; rel=side; type="image/jpeg"',
				[
					['<http://.../side.jpeg?test=1>' => '', 'rel' => 'side', 'type' => 'image/jpeg'],
					['<http://.../side.jpeg?test=2>' => '', 'rel' => 'side', 'type' => 'image/jpeg'],
				],
			],
			[
				'',
				[],
			],
		];
	}

	public function testAllowsArrayInConstructor() {
		$h = new Header('Foo', ['Testing', '123', 'Foo=baz']);
		$this->assertEquals(['Testing', '123', 'Foo=baz'], $h->toArray());
	}

	public function testAllowsFalseyValues() {
		// Allows 0
		$h = new Header('Foo', 0, ';');
		$this->assertEquals('0', (string) $h);
		$this->assertEquals(1, count($h));
		$this->assertEquals(';', $h->getGlue());

		// Does not add a null header by default
		$h = new Header('Foo');
		$this->assertEquals('', (string) $h);
		$this->assertEquals(0, count($h));

		// Allows null array for a single null header
		$h = new Header('Foo', [null]);
		$this->assertEquals('', (string) $h);

		// Allows empty string
		$h = new Header('Foo', '');
		$this->assertEquals('', (string) $h);
		$this->assertEquals(1, count($h));
		$this->assertEquals(1, count($h->normalize()->toArray()));
	}

	public function testCanBeIterated() {
		$h = new Header('Zoo', $this->test);
		$results = [];
		foreach ($h as $key => $value) {
			$results[$key] = $value;
		}
		$this->assertEquals([
			'foo', 'Foo', 'bar',
		], $results);
	}

	public function testCanRemoveValues() {
		$h = new Header('Foo', ['Foo', 'baz', 'bar']);
		$h->removeValue('bar');
		$this->assertTrue($h->hasValue('Foo'));
		$this->assertFalse($h->hasValue('bar'));
		$this->assertTrue($h->hasValue('baz'));
	}

	public function testCanSearchForValues() {
		$h = new Header('Zoo', $this->test);
		$this->assertTrue($h->hasValue('foo'));
		$this->assertTrue($h->hasValue('Foo'));
		$this->assertTrue($h->hasValue('bar'));
		$this->assertFalse($h->hasValue('moo'));
		$this->assertFalse($h->hasValue('FoO'));
	}

	public function testConvertsToString() {
		$i = new Header('Zoo', $this->test);
		$this->assertEquals('foo, Foo, bar', (string) $i);
		$i->setGlue(';');
		$this->assertEquals('foo; Foo; bar', (string) $i);
	}

	public function testIsCountable() {
		$h = new Header('Zoo', $this->test);
		$this->assertEquals(3, count($h));
	}

	public function testNormalizesGluedHeaders() {
		$h = new Header('Zoo', ['foo, Faz', 'bar']);
		$result = $h->normalize(true)->toArray();
		natsort($result);
		$this->assertEquals(['bar', 'foo', 'Faz'], $result);
	}

	/**
	 * @dataProvider parseParamsProvider
	 */
	public function testParseParams($header, $result) {
		$response = new Response(200, ['Link' => $header]);
		$this->assertEquals($result, $response->getHeader('Link')->parseParams());
	}

	public function testStoresHeaderName() {
		$i = new Header('Zoo', $this->test);
		$this->assertEquals('Zoo', $i->getName());
	}
}
