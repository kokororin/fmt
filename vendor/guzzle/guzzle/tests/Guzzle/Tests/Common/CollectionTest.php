<?php

namespace Guzzle\Tests\Common;

use Guzzle\Common\Collection;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Http\QueryString;

/**
 * @covers Guzzle\Common\Collection
 */
class CollectionTest extends \Guzzle\Tests\GuzzleTestCase {
	/** @var Collection */
	protected $coll;

	public function dataProvider() {
		return [
			['this_is_a_test', '{a}_is_a_{b}', [
				'a' => 'this',
				'b' => 'test',
			]],
			['this_is_a_test', '{abc}_is_a_{0}', [
				'abc' => 'this',
				0 => 'test',
			]],
			['this_is_a_test', '{abc}_is_a_{0}', [
				'abc' => 'this',
				0 => 'test',
			]],
			['this_is_a_test', 'this_is_a_test', [
				'abc' => 'this',
			]],
			['{abc}_is_{not_found}a_{0}', '{abc}_is_{not_found}a_{0}', []],
		];
	}

	public function falseyDataProvider() {
		return [
			[false, false],
			[null, null],
			['', ''],
			[[], []],
			[0, 0],
		];
	}

	public function getPathProvider() {
		$data = [
			'foo' => 'bar',
			'baz' => [
				'mesa' => [
					'jar' => 'jar',
					'array' => ['a', 'b', 'c'],
				],
				'bar' => [
					'baz' => 'bam',
					'array' => ['d', 'e', 'f'],
				],
			],
			'bam' => [
				['foo' => 1],
				['foo' => 2],
				['array' => ['h', 'i']],
			],
		];
		$c = new Collection($data);

		return [
			// Simple path selectors
			[$c, 'foo', 'bar'],
			[$c, 'baz', $data['baz']],
			[$c, 'bam', $data['bam']],
			[$c, 'baz/mesa', $data['baz']['mesa']],
			[$c, 'baz/mesa/jar', 'jar'],
			// Merge everything two levels under baz
			[$c, 'baz/*', [
				'jar' => 'jar',
				'array' => array_merge($data['baz']['mesa']['array'], $data['baz']['bar']['array']),
				'baz' => 'bam',
			]],
			// Does not barf on missing keys
			[$c, 'fefwfw', null],
			// Does not barf when a wildcard does not resolve correctly
			[$c, '*/*/*/*/*/wefwfe', []],
			// Allows custom separator
			[$c, '*|mesa', $data['baz']['mesa'], '|'],
			// Merge all 'array' keys two levels under baz (the trailing * does not hurt the results)
			[$c, 'baz/*/array/*', array_merge($data['baz']['mesa']['array'], $data['baz']['bar']['array'])],
			// Merge all 'array' keys two levels under baz
			[$c, 'baz/*/array', array_merge($data['baz']['mesa']['array'], $data['baz']['bar']['array'])],
			[$c, 'baz/mesa/array', $data['baz']['mesa']['array']],
			// Having a trailing * does not hurt the results
			[$c, 'baz/mesa/array/*', $data['baz']['mesa']['array']],
			// Merge of anything one level deep
			[$c, '*', array_merge(['bar'], $data['baz'], $data['bam'])],
			// Funky merge of anything two levels deep
			[$c, '*/*', [
				'jar' => 'jar',
				'array' => ['a', 'b', 'c', 'd', 'e', 'f', 'h', 'i'],
				'baz' => 'bam',
				'foo' => [1, 2],
			]],
			// Funky merge of all 'array' keys that are two levels deep
			[$c, '*/*/array', ['a', 'b', 'c', 'd', 'e', 'f', 'h', 'i']],
		];
	}

	public function testAddParamsByMerging() {
		$params = [
			'test' => 'value1',
			'test2' => 'value2',
			'test3' => ['value3', 'value4'],
		];

		// Add some parameters
		$this->coll->merge($params);

		// Add more parameters by merging them in
		$this->coll->merge([
			'test' => 'another',
			'different_key' => 'new value',
		]);

		$this->assertEquals([
			'test' => ['value1', 'another'],
			'test2' => 'value2',
			'test3' => ['value3', 'value4'],
			'different_key' => 'new value',
		], $this->coll->getAll());
	}

	public function testAllowsFunctionalFilter() {
		$this->coll->merge([
			'fruit' => 'apple',
			'number' => 'ten',
			'prepositions' => ['about', 'above', 'across', 'after'],
			'same_number' => 'ten',
		]);

		$filtered = $this->coll->filter(function ($key, $value) {
			return 'ten' == $value;
		});

		$this->assertNotEquals($filtered, $this->coll);

		$this->assertEquals([
			'number' => 'ten',
			'same_number' => 'ten',
		], $filtered->getAll());
	}

	public function testAllowsFunctionalMapping() {
		$this->coll->merge([
			'number_1' => 1,
			'number_2' => 2,
			'number_3' => 3,
		]);

		$mapped = $this->coll->map(function ($key, $value) {
			return $value * $value;
		});

		$this->assertNotEquals($mapped, $this->coll);

		$this->assertEquals([
			'number_1' => 1,
			'number_2' => 4,
			'number_3' => 9,
		], $mapped->getAll());
	}

	public function testCanAddValuesToExistingKeysByUsingArray() {
		$this->coll->add('test', 'value1');
		$this->assertEquals($this->coll->getAll(), ['test' => 'value1']);
		$this->coll->add('test', 'value2');
		$this->assertEquals($this->coll->getAll(), ['test' => ['value1', 'value2']]);
		$this->coll->add('test', 'value3');
		$this->assertEquals($this->coll->getAll(), ['test' => ['value1', 'value2', 'value3']]);
	}

	public function testCanClearAllDataOrSpecificKeys() {
		$this->coll->merge([
			'test' => 'value1',
			'test2' => 'value2',
		]);

		// Clear a specific parameter by name
		$this->coll->remove('test');

		$this->assertEquals($this->coll->getAll(), [
			'test2' => 'value2',
		]);

		// Clear all parameters
		$this->coll->clear();

		$this->assertEquals($this->coll->getAll(), []);
	}

	public function testCanGetAllValuesByArray() {
		$this->coll->add('foo', 'bar');
		$this->coll->add('tEsT', 'value');
		$this->coll->add('tesTing', 'v2');
		$this->coll->add('key', 'v3');
		$this->assertNull($this->coll->get('test'));
		$this->assertEquals([
			'foo' => 'bar',
			'tEsT' => 'value',
			'tesTing' => 'v2',
		], $this->coll->getAll([
			'foo', 'tesTing', 'tEsT',
		]));
	}

	public function testCanReplaceAllData() {
		$this->assertSame($this->coll, $this->coll->replace([
			'a' => '123',
		]));

		$this->assertEquals([
			'a' => '123',
		], $this->coll->getAll());
	}

	public function testCanSearchByKey() {
		$collection = new Collection([
			'foo' => 'bar',
			'BaZ' => 'pho',
		]);

		$this->assertEquals('foo', $collection->keySearch('FOO'));
		$this->assertEquals('BaZ', $collection->keySearch('baz'));
		$this->assertEquals(false, $collection->keySearch('Bar'));
	}

	public function testCanSetNestedPathValueThatDoesNotExist() {
		$c = new Collection([]);
		$c->setPath('foo/bar/baz/123', 'hi');
		$this->assertEquals('hi', $c['foo']['bar']['baz']['123']);
	}

	public function testCanSetNestedPathValueThatExists() {
		$c = new Collection(['foo' => ['bar' => 'test']]);
		$c->setPath('foo/bar', 'hi');
		$this->assertEquals('hi', $c['foo']['bar']);
	}

	public function testChecksIfHasKey() {
		$this->assertFalse($this->coll->hasKey('test'));
		$this->coll->add('test', 'value');
		$this->assertEquals(true, $this->coll->hasKey('test'));
		$this->coll->add('test2', 'value2');
		$this->assertEquals(true, $this->coll->hasKey('test'));
		$this->assertEquals(true, $this->coll->hasKey('test2'));
		$this->assertFalse($this->coll->hasKey('testing'));
		$this->assertEquals(false, $this->coll->hasKey('AB-C', 'junk'));
	}

	public function testChecksIfHasValue() {
		$this->assertFalse($this->coll->hasValue('value'));
		$this->coll->add('test', 'value');
		$this->assertEquals('test', $this->coll->hasValue('value'));
		$this->coll->add('test2', 'value2');
		$this->assertEquals('test', $this->coll->hasValue('value'));
		$this->assertEquals('test2', $this->coll->hasValue('value2'));
		$this->assertFalse($this->coll->hasValue('val'));
	}

	public function testConstructorCanBeCalledWithNoParams() {
		$this->coll = new Collection();
		$p = $this->coll->getAll();
		$this->assertEmpty($p, '-> Collection must be empty when no data is passed');
	}

	public function testConstructorCanBeCalledWithParams() {
		$testData = [
			'test' => 'value',
			'test_2' => 'value2',
		];
		$this->coll = new Collection($testData);
		$this->assertEquals($this->coll->getAll(), $testData, '-> getAll() must return the data passed in the constructor');
		$this->assertEquals($this->coll->getAll(), $this->coll->toArray());
	}

	public function testFalseyKeysStillDescend() {
		$collection = new Collection([
			'0' => [
				'a' => 'jar',
			],
			1 => 'other',
		]);
		$this->assertEquals('jar', $collection->getPath('0/a'));
		$this->assertEquals('other', $collection->getPath('1'));
	}

	/**
	 * @dataProvider getPathProvider
	 */
	public function testGetPath(Collection $c, $path, $expected, $separator = '/') {
		$this->assertEquals($expected, $c->getPath($path, $separator));
	}

	public function testGetsValuesByKey() {
		$this->assertNull($this->coll->get('test'));
		$this->coll->add('test', 'value');
		$this->assertEquals('value', $this->coll->get('test'));
		$this->coll->set('test2', 'v2');
		$this->coll->set('test3', 'v3');
		$this->assertEquals([
			'test' => 'value',
			'test2' => 'v2',
		], $this->coll->getAll(['test', 'test2']));
	}

	public function testHandlesMergingInDisparateDataSources() {
		$params = [
			'test' => 'value1',
			'test2' => 'value2',
			'test3' => ['value3', 'value4'],
		];
		$this->coll->merge($params);
		$this->assertEquals($this->coll->getAll(), $params);

		// Pass the same object to itself
		$this->assertEquals($this->coll->merge($this->coll), $this->coll);
	}

	public function testImplementsArrayAccess() {
		$this->coll->merge([
			'k1' => 'v1',
			'k2' => 'v2',
		]);

		$this->assertTrue($this->coll->offsetExists('k1'));
		$this->assertFalse($this->coll->offsetExists('Krull'));

		$this->coll->offsetSet('k3', 'v3');
		$this->assertEquals('v3', $this->coll->offsetGet('k3'));
		$this->assertEquals('v3', $this->coll->get('k3'));

		$this->coll->offsetUnset('k1');
		$this->assertFalse($this->coll->offsetExists('k1'));
	}

	public function testImplementsCount() {
		$data = new Collection();
		$this->assertEquals(0, $data->count());
		$data->add('key', 'value');
		$this->assertEquals(1, count($data));
		$data->add('key', 'value2');
		$this->assertEquals(1, count($data));
		$data->add('key_2', 'value3');
		$this->assertEquals(2, count($data));
	}

	public function testImplementsIteratorAggregate() {
		$this->coll->set('key', 'value');
		$this->assertInstanceOf('ArrayIterator', $this->coll->getIterator());
		$this->assertEquals(1, count($this->coll));
		$total = 0;
		foreach ($this->coll as $key => $value) {
			$this->assertEquals('key', $key);
			$this->assertEquals('value', $value);
			++$total;
		}
		$this->assertEquals(1, $total);
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testInjectsConfigData($output, $input, $config) {
		$collection = new Collection($config);
		$this->assertEquals($output, $collection->inject($input));
	}

	public function testOverridesSettings() {
		$c = new Collection(['foo' => 1, 'baz' => 2, 'bar' => 3]);
		$c->overwriteWith(['foo' => 10, 'bar' => 300]);
		$this->assertEquals(['foo' => 10, 'baz' => 2, 'bar' => 300], $c->getAll());
	}

	public function testOverwriteWithCollection() {
		$c = new Collection(['foo' => 1, 'baz' => 2, 'bar' => 3]);
		$b = new Collection(['foo' => 10, 'bar' => 300]);
		$c->overwriteWith($b);
		$this->assertEquals(['foo' => 10, 'baz' => 2, 'bar' => 300], $c->getAll());
	}

	public function testOverwriteWithTraversable() {
		$c = new Collection(['foo' => 1, 'baz' => 2, 'bar' => 3]);
		$b = new Collection(['foo' => 10, 'bar' => 300]);
		$c->overwriteWith($b->getIterator());
		$this->assertEquals(['foo' => 10, 'baz' => 2, 'bar' => 300], $c->getAll());
	}

	public function testPreparesFromConfig() {
		$c = Collection::fromConfig([
			'a' => '123',
			'base_url' => 'http://www.test.com/',
		], [
			'a' => 'xyz',
			'b' => 'lol',
		], ['a']);

		$this->assertInstanceOf('Guzzle\Common\Collection', $c);
		$this->assertEquals([
			'a' => '123',
			'b' => 'lol',
			'base_url' => 'http://www.test.com/',
		], $c->getAll());

		try {
			$c = Collection::fromConfig([], [], ['a']);
			$this->fail('Exception not throw when missing config');
		} catch (InvalidArgumentException $e) {
		}
	}

	public function testProvidesKeys() {
		$this->assertEquals([], $this->coll->getKeys());
		$this->coll->merge([
			'test1' => 'value1',
			'test2' => 'value2',
		]);
		$this->assertEquals(['test1', 'test2'], $this->coll->getKeys());
		// Returns the cached array previously returned
		$this->assertEquals(['test1', 'test2'], $this->coll->getKeys());
		$this->coll->remove('test1');
		$this->assertEquals(['test2'], $this->coll->getKeys());
		$this->coll->add('test3', 'value3');
		$this->assertEquals(['test2', 'test3'], $this->coll->getKeys());
	}

	public function testRetrievesNestedKeysUsingPath() {
		$data = [
			'foo' => 'bar',
			'baz' => [
				'mesa' => [
					'jar' => 'jar',
				],
			],
		];
		$collection = new Collection($data);
		$this->assertEquals('bar', $collection->getPath('foo'));
		$this->assertEquals('jar', $collection->getPath('baz/mesa/jar'));
		$this->assertNull($collection->getPath('wewewf'));
		$this->assertNull($collection->getPath('baz/mesa/jar/jar'));
	}

	/**
	 * @dataProvider falseyDataProvider
	 */
	public function testReturnsCorrectData($a, $b) {
		$c = new Collection(['value' => $a]);
		$this->assertSame($b, $c->get('value'));
	}

	public function testUsesStaticWhenCreatingNew() {
		$qs = new QueryString([
			'a' => 'b',
			'c' => 'd',
		]);

		$this->assertInstanceOf('Guzzle\\Http\\QueryString', $qs->map(function ($a, $b) {}));
		$this->assertInstanceOf('Guzzle\\Common\\Collection', $qs->map(function ($a, $b) {}, [], false));

		$this->assertInstanceOf('Guzzle\\Http\\QueryString', $qs->filter(function ($a, $b) {}));
		$this->assertInstanceOf('Guzzle\\Common\\Collection', $qs->filter(function ($a, $b) {}, false));
	}

	/**
	 * @expectedException \Guzzle\Common\Exception\RuntimeException
	 */
	public function testVerifiesNestedPathIsValidAtExactLevel() {
		$c = new Collection(['foo' => 'bar']);
		$c->setPath('foo/bar', 'hi');
		$this->assertEquals('hi', $c['foo']['bar']);
	}

	/**
	 * @expectedException \Guzzle\Common\Exception\RuntimeException
	 */
	public function testVerifiesThatNestedPathIsValidAtAnyLevel() {
		$c = new Collection(['foo' => 'bar']);
		$c->setPath('foo/bar/baz', 'test');
	}

	protected function setUp() {
		$this->coll = new Collection();
	}
}
