<?php

namespace Guzzle\Tests\Cache;

use Doctrine\Common\Cache\ArrayCache;
use Guzzle\Cache\CacheAdapterFactory;
use Guzzle\Cache\DoctrineCacheAdapter;
use Zend\Cache\StorageFactory;

/**
 * @covers Guzzle\Cache\CacheAdapterFactory
 */
class CacheAdapterFactoryTest extends \Guzzle\Tests\GuzzleTestCase {
	/** @var DoctrineCacheAdapter */
	private $adapter;

	/** @var ArrayCache */
	private $cache;

	public function cacheProvider() {
		return [
			[new DoctrineCacheAdapter(new ArrayCache()), 'Guzzle\Cache\DoctrineCacheAdapter'],
			[new ArrayCache(), 'Guzzle\Cache\DoctrineCacheAdapter'],
			[StorageFactory::factory(['adapter' => 'memory']), 'Guzzle\Cache\Zf2CacheAdapter'],
		];
	}

	/**
	 * @dataProvider cacheProvider
	 */
	public function testCreatesNullCacheAdapterByDefault($cache, $type) {
		$adapter = CacheAdapterFactory::fromCache($cache);
		$this->assertInstanceOf($type, $adapter);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testEnsuresConfigIsObject() {
		CacheAdapterFactory::fromCache([]);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testEnsuresKnownType() {
		CacheAdapterFactory::fromCache(new \stdClass());
	}

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setup() {
		parent::setUp();
		$this->cache = new ArrayCache();
		$this->adapter = new DoctrineCacheAdapter($this->cache);
	}
}
