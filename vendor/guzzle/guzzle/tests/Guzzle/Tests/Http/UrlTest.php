<?php

namespace Guzzle\Tests\Http;

use Guzzle\Http\QueryString;
use Guzzle\Http\Url;

/**
 * @covers Guzzle\Http\Url
 */
class UrlTest extends \Guzzle\Tests\GuzzleTestCase {
	/**
	 * @link http://tools.ietf.org/html/rfc3986#section-5.4.1
	 */
	public function rfc3986UrlProvider() {
		$result = [
			['g', 'http://a/b/c/g'],
			['./g', 'http://a/b/c/g'],
			['g/', 'http://a/b/c/g/'],
			['/g', 'http://a/g'],
			['?y', 'http://a/b/c/d;p?y'],
			['g?y', 'http://a/b/c/g?y'],
			['#s', 'http://a/b/c/d;p?q#s'],
			['g#s', 'http://a/b/c/g#s'],
			['g?y#s', 'http://a/b/c/g?y#s'],
			[';x', 'http://a/b/c/;x'],
			['g;x', 'http://a/b/c/g;x'],
			['g;x?y#s', 'http://a/b/c/g;x?y#s'],
			['', 'http://a/b/c/d;p?q'],
			['.', 'http://a/b/c'],
			['./', 'http://a/b/c/'],
			['..', 'http://a/b'],
			['../', 'http://a/b/'],
			['../g', 'http://a/b/g'],
			['../..', 'http://a/'],
			['../../', 'http://a/'],
			['../../g', 'http://a/g'],
		];

		// This support was added in PHP 5.4.7: https://bugs.php.net/bug.php?id=62844
		if (version_compare(PHP_VERSION, '5.4.7', '>=')) {
			$result[] = ['//g', 'http://g'];
		}

		return $result;
	}

	public function testAddsQueryStringIfPresent() {
		$this->assertEquals('?foo=bar', Url::buildUrl([
			'query' => 'foo=bar',
		]));
	}

	public function testAddsToPath() {
		// Does nothing here
		$this->assertEquals('http://e.com/base?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath(false));
		$this->assertEquals('http://e.com/base?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath(null));
		$this->assertEquals('http://e.com/base?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath([]));
		$this->assertEquals('http://e.com/base?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath(new \stdClass()));
		$this->assertEquals('http://e.com/base?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath(''));
		$this->assertEquals('http://e.com/base?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath('/'));
		$this->assertEquals('http://e.com/baz/foo', (string) Url::factory('http://e.com/baz/')->addPath('foo'));
		$this->assertEquals('http://e.com/base/relative?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath('relative'));
		$this->assertEquals('http://e.com/base/relative?a=1', (string) Url::factory('http://e.com/base?a=1')->addPath('/relative'));
		$this->assertEquals('http://e.com/base/0', (string) Url::factory('http://e.com/base')->addPath('0'));
		$this->assertEquals('http://e.com/base/0/1', (string) Url::factory('http://e.com/base')->addPath('0')->addPath('1'));
	}

	public function testAllowsFalsyUrlParts() {
		$url = Url::factory('http://0:50/0?0#0');
		$this->assertSame('0', $url->getHost());
		$this->assertEquals(50, $url->getPort());
		$this->assertSame('/0', $url->getPath());
		$this->assertEquals('0', (string) $url->getQuery());
		$this->assertSame('0', $url->getFragment());
		$this->assertEquals('http://0:50/0?0#0', (string) $url);

		$url = Url::factory('');
		$this->assertSame('', (string) $url);

		$url = Url::factory('0');
		$this->assertSame('0', (string) $url);
	}

	public function testBuildsRelativeUrlsWithFalsyParts() {
		$url = Url::buildUrl([
			'host' => '0',
			'path' => '0',
		]);

		$this->assertSame('//0/0', $url);

		$url = Url::buildUrl([
			'path' => '0',
		]);
		$this->assertSame('0', $url);
	}

	public function testCloneCreatesNewInternalObjects() {
		$u1 = Url::factory('http://www.test.com/');
		$u2 = clone $u1;
		$this->assertNotSame($u1->getQuery(), $u2->getQuery());
	}

	/**
	 * @dataProvider urlCombineDataProvider
	 */
	public function testCombinesUrls($a, $b, $c) {
		$this->assertEquals($c, (string) Url::factory($a)->combine($b));
	}

	/**
	 * @dataProvider rfc3986UrlProvider
	 */
	public function testCombinesUrlsUsingRfc3986($relative, $result) {
		$a = Url::factory('http://a/b/c/d;p?q');
		$b = Url::factory($relative);
		$this->assertEquals($result, trim((string) $a->combine($b, true), '='));
	}

	public function testConvertsSpecialCharsInPathWhenCastingToString() {
		$url = Url::factory('http://foo.com/baz bar?a=b');
		$url->addPath('?');
		$this->assertEquals('http://foo.com/baz%20bar/%3F?a=b', (string) $url);
	}

	public function testEmptyUrl() {
		$url = Url::factory('');
		$this->assertEquals('', (string) $url);
	}

	public function testHandlesPathsCorrectly() {
		$url = Url::factory('http://www.test.com');
		$this->assertEquals('', $url->getPath());
		$url->setPath('test');
		$this->assertEquals('test', $url->getPath());

		$url->setPath('/test/123/abc');
		$this->assertEquals(['test', '123', 'abc'], $url->getPathSegments());

		$parts = parse_url('http://www.test.com/test');
		$parts['path'] = '';
		$this->assertEquals('http://www.test.com', Url::buildUrl($parts));
		$parts['path'] = 'test';
		$this->assertEquals('http://www.test.com/test', Url::buildUrl($parts));
	}

	public function testHasGettersAndSetters() {
		$url = Url::factory('http://www.test.com/');
		$this->assertEquals('example.com', $url->setHost('example.com')->getHost());
		$this->assertEquals('8080', $url->setPort(8080)->getPort());
		$this->assertEquals('/foo/bar', $url->setPath(['foo', 'bar'])->getPath());
		$this->assertEquals('a', $url->setPassword('a')->getPassword());
		$this->assertEquals('b', $url->setUsername('b')->getUsername());
		$this->assertEquals('abc', $url->setFragment('abc')->getFragment());
		$this->assertEquals('https', $url->setScheme('https')->getScheme());
		$this->assertEquals('a=123', (string) $url->setQuery('a=123')->getQuery());
		$this->assertEquals('https://b:a@example.com:8080/foo/bar?a=123#abc', (string) $url);
		$this->assertEquals('b=boo', (string) $url->setQuery(new QueryString([
			'b' => 'boo',
		]))->getQuery());
		$this->assertEquals('https://b:a@example.com:8080/foo/bar?b=boo#abc', (string) $url);
	}

	/**
	 * @dataProvider urlProvider
	 */
	public function testNormalizesPaths($path, $result) {
		$url = Url::factory('http://www.example.com/');
		$url->setPath($path)->normalizePath();
		$this->assertEquals($result, $url->getPath());
	}

	public function testPortIsDeterminedFromScheme() {
		$this->assertEquals(80, Url::factory('http://www.test.com/')->getPort());
		$this->assertEquals(443, Url::factory('https://www.test.com/')->getPort());
		$this->assertEquals(null, Url::factory('ftp://www.test.com/')->getPort());
		$this->assertEquals(8192, Url::factory('http://www.test.com:8192/')->getPort());
	}

	public function testSetQueryAcceptsArray() {
		$url = Url::factory('http://www.test.com');
		$url->setQuery(['a' => 'b']);
		$this->assertEquals('http://www.test.com?a=b', (string) $url);
	}

	public function testSettingHostWithPortModifiesPort() {
		$url = Url::factory('http://www.example.com');
		$url->setHost('foo:8983');
		$this->assertEquals('foo', $url->getHost());
		$this->assertEquals(8983, $url->getPort());
	}

	public function testUrlStoresParts() {
		$url = Url::factory('http://test:pass@www.test.com:8081/path/path2/?a=1&b=2#fragment');
		$this->assertEquals('http', $url->getScheme());
		$this->assertEquals('test', $url->getUsername());
		$this->assertEquals('pass', $url->getPassword());
		$this->assertEquals('www.test.com', $url->getHost());
		$this->assertEquals(8081, $url->getPort());
		$this->assertEquals('/path/path2/', $url->getPath());
		$this->assertEquals('fragment', $url->getFragment());
		$this->assertEquals('a=1&b=2', (string) $url->getQuery());

		$this->assertEquals([
			'fragment' => 'fragment',
			'host' => 'www.test.com',
			'pass' => 'pass',
			'path' => '/path/path2/',
			'port' => 8081,
			'query' => 'a=1&b=2',
			'scheme' => 'http',
			'user' => 'test',
		], $url->getParts());
	}

	/**
	 * @expectedException \Guzzle\Common\Exception\InvalidArgumentException
	 */
	public function testValidatesUrlCanBeParsed() {
		Url::factory('foo:////');
	}

	public function testValidatesUrlPartsInFactory() {
		$url = Url::factory('/index.php');
		$this->assertEquals('/index.php', (string) $url);
		$this->assertFalse($url->isAbsolute());

		$url = 'http://michael:test@test.com:80/path/123?q=abc#test';
		$u = Url::factory($url);
		$this->assertEquals('http://michael:test@test.com/path/123?q=abc#test', (string) $u);
		$this->assertTrue($u->isAbsolute());
	}

	/**
	 * URL combination data provider
	 *
	 * @return array
	 */
	public function urlCombineDataProvider() {
		return [
			['http://www.example.com/', 'http://www.example.com/', 'http://www.example.com/'],
			['http://www.example.com/path', '/absolute', 'http://www.example.com/absolute'],
			['http://www.example.com/path', '/absolute?q=2', 'http://www.example.com/absolute?q=2'],
			['http://www.example.com/path', 'more', 'http://www.example.com/path/more'],
			['http://www.example.com/path', 'more?q=1', 'http://www.example.com/path/more?q=1'],
			['http://www.example.com/', '?q=1', 'http://www.example.com/?q=1'],
			['http://www.example.com/path', 'http://test.com', 'http://test.com'],
			['http://www.example.com:8080/path', 'http://test.com', 'http://test.com'],
			['http://www.example.com:8080/path', '?q=2#abc', 'http://www.example.com:8080/path?q=2#abc'],
			['http://u:a@www.example.com/path', 'test', 'http://u:a@www.example.com/path/test'],
			['http://www.example.com/path', 'http://u:a@www.example.com/', 'http://u:a@www.example.com/'],
			['/path?q=2', 'http://www.test.com/', 'http://www.test.com/path?q=2'],
			['http://api.flickr.com/services/', 'http://www.flickr.com/services/oauth/access_token', 'http://www.flickr.com/services/oauth/access_token'],
			['http://www.example.com/?foo=bar', 'some/path', 'http://www.example.com/some/path?foo=bar'],
			['http://www.example.com/?foo=bar', 'some/path?boo=moo', 'http://www.example.com/some/path?boo=moo&foo=bar'],
			['http://www.example.com/some/', 'path?foo=bar&foo=baz', 'http://www.example.com/some/path?foo=bar&foo=baz'],
		];
	}

	public function urlProvider() {
		return [
			['/foo/..', '/'],
			['//foo//..', '/'],
			['/foo/../..', '/'],
			['/foo/../.', '/'],
			['/./foo/..', '/'],
			['/./foo', '/foo'],
			['/./foo/', '/foo/'],
			['/./foo/bar/baz/pho/../..', '/foo/bar'],
			['*', '*'],
			['/foo', '/foo'],
			['/abc/123/../foo/', '/abc/foo/'],
			['/a/b/c/./../../g', '/a/g'],
			['/b/c/./../../g', '/g'],
			['/b/c/./../../g', '/g'],
			['/c/./../../g', '/g'],
			['/./../../g', '/g'],
		];
	}
}
