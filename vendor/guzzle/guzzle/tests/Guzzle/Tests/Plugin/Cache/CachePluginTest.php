<?php

namespace Guzzle\Tests\Plugin\Cache;

use Doctrine\Common\Cache\ArrayCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Common\Event;
use Guzzle\Common\Version;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\CallbackCanCacheStrategy;
use Guzzle\Plugin\Cache\DefaultCacheStorage;

/**
 * @group server
 * @covers Guzzle\Plugin\Cache\CachePlugin
 * @covers Guzzle\Plugin\Cache\DefaultRevalidation
 */
class CachePluginTest extends \Guzzle\Tests\GuzzleTestCase {
	public function satisfiableOnErrorProvider() {
		$date = new \DateTime('-10 seconds');
		return [
			[
				new Response(200, [
					'Date' => $date->format('c'),
					'Cache-Control' => 'max-age=5, stale-if-error',
				], 'foo'),
			],
		];
	}

	public function satisfiableProvider() {
		$date = new \DateTime('-10 seconds');

		return [
			// Fresh response
			[new Response(200, [], 'foo')],
			// Stale response
			[new Response(200, ['Date' => $date->format('c'), 'Cache-Control' => 'max-age=5'], 'foo')],
		];
	}

	public function satisfyFailedProvider() {
		return [
			// Neither has stale-if-error
			[new Request('GET', 'http://foo.com', []), new Response(200, ['Age' => 100]), false],
			// Request has stale-if-error
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'stale-if-error']), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50']), true],
			// Request has valid stale-if-error
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'stale-if-error=50']), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50']), true],
			// Request has expired stale-if-error
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'stale-if-error=20']), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50']), false],
			// Response has permanent stale-if-error
			[new Request('GET', 'http://foo.com', []), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50, stale-if-error']), true],
			// Response has valid stale-if-error
			[new Request('GET', 'http://foo.com', []), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50, stale-if-error=50']), true],
			// Response has expired stale-if-error
			[new Request('GET', 'http://foo.com', []), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50, stale-if-error=20']), false],
			// Request has valid stale-if-error but response does not
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'stale-if-error=50']), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50, stale-if-error=20']), false],
			// Response has valid stale-if-error but request does not
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'stale-if-error=20']), new Response(200, ['Age' => 100, 'Cache-Control' => 'max-age=50, stale-if-error=50']), false],
		];
	}

	public function satisfyProvider() {
		$req1 = new Request('GET', 'http://foo.com', ['Cache-Control' => 'no-cache']);

		return [
			// The response is too old to satisfy the request
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'max-age=20']), new Response(200, ['Age' => 100]), false, false],
			// The response cannot satisfy the request because it is stale
			[new Request('GET', 'http://foo.com'), new Response(200, ['Cache-Control' => 'max-age=10', 'Age' => 100]), false, false],
			// Allows the expired response to satisfy the request because of the max-stale
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'max-stale=15']), new Response(200, ['Cache-Control' => 'max-age=90', 'Age' => 100]), true, false],
			// Max stale is > than the allowed staleness
			[new Request('GET', 'http://foo.com', ['Cache-Control' => 'max-stale=5']), new Response(200, ['Cache-Control' => 'max-age=90', 'Age' => 100]), false, false],
			// Performs cache revalidation
			[$req1, new Response(200), true, true],
			// Performs revalidation due to ETag on the response and no cache-control on the request
			[new Request('GET', 'http://foo.com'), new Response(200, [
				'ETag' => 'ABC',
				'Expires' => date('c', strtotime('+1 year')),
			]), true, true],
		];
	}

	public function testAddsCallbackCollaborators() {
		$this->assertNotEmpty(CachePlugin::getSubscribedEvents());
		$plugin = new CachePlugin(['can_cache' => function () {}]);
		$this->assertInstanceOf(
			'Guzzle\Plugin\Cache\CallbackCanCacheStrategy',
			$this->readAttribute($plugin, 'canCache')
		);
	}

	public function testAddsDefaultCollaborators() {
		$this->assertNotEmpty(CachePlugin::getSubscribedEvents());
		$plugin = new CachePlugin([
			'storage' => $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')->getMockForAbstractClass(),
		]);
		$this->assertInstanceOf('Guzzle\Plugin\Cache\CacheStorageInterface', $this->readAttribute($plugin, 'storage'));
		$this->assertInstanceOf(
			'Guzzle\Plugin\Cache\CanCacheStrategyInterface',
			$this->readAttribute($plugin, 'canCache')
		);
		$this->assertInstanceOf(
			'Guzzle\Plugin\Cache\RevalidationInterface',
			$this->readAttribute($plugin, 'revalidation')
		);
	}

	public function testAddsDefaultStorage() {
		$plugin = new CachePlugin();
		$this->assertInstanceOf('Guzzle\Plugin\Cache\CacheStorageInterface', $this->readAttribute($plugin, 'storage'));
	}

	public function testAutoPurgesRequests() {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['purge'])
			->getMockForAbstractClass();
		$storage->expects($this->atLeastOnce())->method('purge');
		$plugin = new CachePlugin(['storage' => $storage, 'auto_purge' => true]);
		$client = new Client();
		$request = $client->put('http://foo.com', ['X-Foo' => 'Bar']);
		$request->addSubscriber($plugin);
		$request->setResponse(new Response(200), true);
		$request->send();
	}

	public function testCachesResponsesWhenCacheable() {
		$cache = new ArrayCache();
		$plugin = new CachePlugin($cache);

		$request = new Request('GET', 'http://foo.com');
		$response = new Response(200, [], 'Foo');
		$plugin->onRequestBeforeSend(new Event([
			'request' => $request,
		]));
		$plugin->onRequestSent(new Event([
			'request' => $request,
			'response' => $response,
		]));
		$data = $this->readAttribute($cache, 'data');
		$this->assertNotEmpty($data);
	}

	public function testCanPassCacheAsOnlyArgumentToConstructor() {
		$p = new CachePlugin(new DoctrineCacheAdapter(new ArrayCache()));
		$p = new CachePlugin(new DefaultCacheStorage(new DoctrineCacheAdapter(new ArrayCache())));
	}

	/**
	 * @dataProvider satisfyFailedProvider
	 */
	public function testChecksIfResponseCanSatisfyFailedRequest($request, $response, $can) {
		$plugin = new CachePlugin();

		$this->assertEquals($can, $plugin->canResponseSatisfyFailedRequest($request, $response));
	}

	/**
	 * @dataProvider satisfyProvider
	 */
	public function testChecksIfResponseCanSatisfyRequest($request, $response, $can, $revalidates) {
		$didRevalidate = false;
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')->getMockForAbstractClass();
		$revalidate = $this->getMockBuilder('Guzzle\Plugin\Cache\DefaultRevalidation')
			->setMethods(['revalidate'])
			->setConstructorArgs([$storage])
			->getMockForAbstractClass();

		$revalidate->expects($this->any())
			->method('revalidate')
			->will($this->returnCallback(function () use (&$didRevalidate) {
				$didRevalidate = true;
				return true;
			}));

		$plugin = new CachePlugin([
			'storage' => $storage,
			'revalidation' => $revalidate,
		]);

		$this->assertEquals($can, $plugin->canResponseSatisfyRequest($request, $response));
		$this->assertEquals($didRevalidate, $revalidates);
	}

	/**
	 * @dataProvider unsatisfiableOnErrorProvider
	 */
	public function testDoesNotInjectUnsatisfiableResponsesOnError($requestCanCache, $requestHeaders, $cacheResponse) {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['fetch'])
			->getMockForAbstractClass();
		$storage->expects($this->exactly($requestCanCache ? 2 : 0))->method('fetch')->will($this->returnValue($cacheResponse));
		$plugin = new CachePlugin(['storage' => $storage]);
		$request = new Request('GET', 'http://foo.com', $requestHeaders);
		$plugin->onRequestBeforeSend(new Event([
			'request' => $request,
		]));
		$plugin->onRequestError(
			$event = new Event([
				'request' => $request,
				'response' => $response = $request->getResponse(),
			])
		);

		$this->assertSame($response, $event['response']);
	}

	/**
	 * @dataProvider unsatisfiableOnErrorProvider
	 */
	public function testDoesNotInjectUnsatisfiableResponsesOnException($requestCanCache, $requestHeaders, $responseParts) {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['fetch'])
			->getMockForAbstractClass();
		$storage->expects($this->exactly($requestCanCache ? 2 : 0))->method('fetch')->will($this->returnValue($responseParts));
		$plugin = new CachePlugin(['storage' => $storage]);
		$request = new Request('GET', 'http://foo.com', $requestHeaders);
		$plugin->onRequestBeforeSend(new Event([
			'request' => $request,
		]));
		$plugin->onRequestException(
			$event = new Event([
				'request' => $request,
				'response' => $response = $request->getResponse(),
				'exception' => $this->getMock('Guzzle\Http\Exception\CurlException'),
			])
		);

		$this->assertSame($response, $request->getResponse());
	}

	public function testDoesNothingWhenRequestIsNotCacheable() {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['fetch'])
			->getMockForAbstractClass();
		$storage->expects($this->never())->method('fetch');

		$plugin = new CachePlugin([
			'storage' => $storage,
			'can_cache' => new CallbackCanCacheStrategy(function () {return false;}),
		]);

		$plugin->onRequestBeforeSend(new Event([
			'request' => new Request('GET', 'http://foo.com'),
		]));
	}

	/**
	 * @dataProvider satisfiableProvider
	 */
	public function testInjectsSatisfiableResponses($response) {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['fetch'])
			->getMockForAbstractClass();

		$storage->expects($this->once())->method('fetch')->will($this->returnValue($response));
		$plugin = new CachePlugin(['storage' => $storage]);
		$request = new Request('GET', 'http://foo.com', ['Cache-Control' => 'max-stale']);
		$plugin->onRequestBeforeSend(new Event(['request' => $request]));
		$plugin->onRequestSent(new Event(['request' => $request, 'response' => $request->getResponse()]));
		$this->assertEquals($response->getStatusCode(), $request->getResponse()->getStatusCode());
		$this->assertEquals((string) $response->getBody(), (string) $request->getResponse()->getBody());
		$this->assertTrue($request->getResponse()->hasHeader('Age'));
		if ($request->getResponse()->isFresh() === false) {
			$this->assertContains('110', (string) $request->getResponse()->getHeader('Warning'));
		}
		$this->assertSame(
			sprintf('%s GuzzleCache/%s', $request->getProtocolVersion(), Version::VERSION),
			(string) $request->getHeader('Via')
		);
		$this->assertSame(
			sprintf('%s GuzzleCache/%s', $request->getProtocolVersion(), Version::VERSION),
			(string) $request->getResponse()->getHeader('Via')
		);
		$this->assertTrue($request->getParams()->get('cache.lookup'));
		$this->assertTrue($request->getParams()->get('cache.hit'));
		$this->assertTrue($request->getResponse()->hasHeader('X-Cache-Lookup'));
		$this->assertTrue($request->getResponse()->hasHeader('X-Cache'));
		$this->assertEquals('HIT from GuzzleCache', (string) $request->getResponse()->getHeader('X-Cache'));
		$this->assertEquals('HIT from GuzzleCache', (string) $request->getResponse()->getHeader('X-Cache-Lookup'));
	}

	/**
	 * @dataProvider satisfiableOnErrorProvider
	 */
	public function testInjectsSatisfiableResponsesOnError($cacheResponse) {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['fetch'])
			->getMockForAbstractClass();
		$storage->expects($this->exactly(2))->method('fetch')->will($this->returnValue($cacheResponse));
		$plugin = new CachePlugin(['storage' => $storage]);
		$request = new Request('GET', 'http://foo.com', ['Cache-Control' => 'max-stale']);
		$plugin->onRequestBeforeSend(new Event(['request' => $request]));
		$plugin->onRequestError(
			$event = new Event([
				'request' => $request,
				'response' => $request->getResponse(),
			])
		);
		$response = $event['response'];
		$this->assertEquals($cacheResponse->getStatusCode(), $response->getStatusCode());
		$this->assertEquals((string) $cacheResponse->getBody(), (string) $response->getBody());
		$this->assertTrue($response->hasHeader('Age'));
		if ($response->isFresh() === false) {
			$this->assertContains('110', (string) $response->getHeader('Warning'));
		}
		$this->assertSame(sprintf('%s GuzzleCache/%s', $request->getProtocolVersion(), Version::VERSION), (string) $request->getHeader('Via'));
		$this->assertSame(sprintf('%s GuzzleCache/%s', $request->getProtocolVersion(), Version::VERSION), (string) $response->getHeader('Via'));
		$this->assertTrue($request->getParams()->get('cache.lookup'));
		$this->assertSame('error', $request->getParams()->get('cache.hit'));
		$this->assertTrue($response->hasHeader('X-Cache-Lookup'));
		$this->assertTrue($response->hasHeader('X-Cache'));
		$this->assertEquals('HIT from GuzzleCache', (string) $response->getHeader('X-Cache-Lookup'));
		$this->assertEquals('HIT_ERROR from GuzzleCache', (string) $response->getHeader('X-Cache'));
	}

	/**
	 * @dataProvider satisfiableOnErrorProvider
	 */
	public function testInjectsSatisfiableResponsesOnException($cacheResponse) {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['fetch'])
			->getMockForAbstractClass();
		$storage->expects($this->exactly(2))->method('fetch')->will($this->returnValue($cacheResponse));
		$plugin = new CachePlugin(['storage' => $storage]);
		$request = new Request('GET', 'http://foo.com', ['Cache-Control' => 'max-stale']);
		$plugin->onRequestBeforeSend(new Event([
			'request' => $request,
		]));
		$plugin->onRequestException(
			new Event([
				'request' => $request,
				'response' => $request->getResponse(),
				'exception' => $this->getMock('Guzzle\Http\Exception\CurlException'),
			])
		);
		$plugin->onRequestSent(
			new Event([
				'request' => $request,
				'response' => $response = $request->getResponse(),
			])
		);
		$this->assertEquals($cacheResponse->getStatusCode(), $response->getStatusCode());
		$this->assertEquals((string) $cacheResponse->getBody(), (string) $response->getBody());
		$this->assertTrue($response->hasHeader('Age'));
		if ($response->isFresh() === false) {
			$this->assertContains('110', (string) $response->getHeader('Warning'));
		}
		$this->assertSame(sprintf('%s GuzzleCache/%s', $request->getProtocolVersion(), Version::VERSION), (string) $request->getHeader('Via'));
		$this->assertSame(sprintf('%s GuzzleCache/%s', $request->getProtocolVersion(), Version::VERSION), (string) $response->getHeader('Via'));
		$this->assertTrue($request->getParams()->get('cache.lookup'));
		$this->assertSame('error', $request->getParams()->get('cache.hit'));
		$this->assertTrue($response->hasHeader('X-Cache-Lookup'));
		$this->assertTrue($response->hasHeader('X-Cache'));
		$this->assertEquals('HIT from GuzzleCache', (string) $response->getHeader('X-Cache-Lookup'));
		$this->assertEquals('HIT_ERROR from GuzzleCache', (string) $response->getHeader('X-Cache'));
	}

	public function testPurgesRequests() {
		$storage = $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')
			->setMethods(['purge'])
			->getMockForAbstractClass();
		$storage->expects($this->atLeastOnce())->method('purge');
		$plugin = new CachePlugin(['storage' => $storage]);
		$request = new Request('GET', 'http://foo.com', ['X-Foo' => 'Bar']);
		$plugin->purge($request);
	}

	public function testUsesCreatedCacheStorage() {
		$plugin = new CachePlugin([
			'adapter' => $this->getMockBuilder('Guzzle\Cache\CacheAdapterInterface')->getMockForAbstractClass(),
		]);
		$this->assertInstanceOf('Guzzle\Plugin\Cache\CacheStorageInterface', $this->readAttribute($plugin, 'storage'));
	}

	public function testUsesProvidedOptions() {
		$can = $this->getMockBuilder('Guzzle\Plugin\Cache\CanCacheStrategyInterface')->getMockForAbstractClass();
		$revalidate = $this->getMockBuilder('Guzzle\Plugin\Cache\RevalidationInterface')->getMockForAbstractClass();
		$plugin = new CachePlugin([
			'storage' => $this->getMockBuilder('Guzzle\Plugin\Cache\CacheStorageInterface')->getMockForAbstractClass(),
			'can_cache' => $can,
			'revalidation' => $revalidate,
		]);
		$this->assertSame($can, $this->readAttribute($plugin, 'canCache'));
		$this->assertSame($revalidate, $this->readAttribute($plugin, 'revalidation'));
	}

	public function unsatisfiableOnErrorProvider() {
		$date = new \DateTime('-10 seconds');

		return [
			// no-store on request
			[
				false,
				['Cache-Control' => 'no-store'],
				new Response(200, ['Date' => $date->format('D, d M Y H:i:s T'), 'Cache-Control' => 'max-age=5, stale-if-error'], 'foo'),
			],
			// request expired
			[
				true,
				['Cache-Control' => 'stale-if-error=4'],
				new Response(200, ['Date' => $date->format('D, d M Y H:i:s T'), 'Cache-Control' => 'max-age=5, stale-if-error'], 'foo'),
			],
			// response expired
			[
				true,
				['Cache-Control' => 'stale-if-error'],
				new Response(200, ['Date' => $date->format('D, d M Y H:i:s T'), 'Cache-Control' => 'max-age=5, stale-if-error=4'], 'foo'),
			],
		];
	}
}
