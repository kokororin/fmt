<?php

namespace Guzzle\Tests\Parser\Cookie;

use Guzzle\Http\Url;

/**
 * @covers Guzzle\Parser\Cookie\CookieParser
 */
class CookieParserProvider extends \Guzzle\Tests\GuzzleTestCase {
	/**
	 * Provides the parsed information from a cookie
	 *
	 * @return array
	 */
	public function cookieParserDataProvider() {
		return [
			[
				'ASIHTTPRequestTestCookie=This+is+the+value; expires=Sat, 26-Jul-2008 17:00:42 GMT; path=/tests; domain=allseeing-i.com; PHPSESSID=6c951590e7a9359bcedde25cda73e43c; path=/";',
				[
					'domain' => 'allseeing-i.com',
					'path' => '/',
					'data' => [
						'PHPSESSID' => '6c951590e7a9359bcedde25cda73e43c',
					],
					'max_age' => NULL,
					'expires' => 'Sat, 26-Jul-2008 17:00:42 GMT',
					'version' => NULL,
					'secure' => NULL,
					'discard' => NULL,
					'port' => NULL,
					'cookies' => [
						'ASIHTTPRequestTestCookie' => 'This+is+the+value',
					],
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			['', false],
			['foo', false],
			// Test setting a blank value for a cookie
			[[
				'foo=', 'foo =', 'foo =;', 'foo= ;', 'foo =', 'foo= '],
				[
					'cookies' => [
						'foo' => '',
					],
					'data' => [],
					'discard' => null,
					'domain' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			// Test setting a value and removing quotes
			[[
				'foo=1', 'foo =1', 'foo =1;', 'foo=1 ;', 'foo =1', 'foo= 1', 'foo = 1 ;', 'foo="1"', 'foo="1";', 'foo= "1";'],
				[
					'cookies' => [
						'foo' => '1',
					],
					'data' => [],
					'discard' => null,
					'domain' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			// Test setting multiple values
			[[
				'foo=1; bar=2;', 'foo =1; bar = "2"', 'foo=1;   bar=2'],
				[
					'cookies' => [
						'foo' => '1',
						'bar' => '2',
					],
					'data' => [],
					'discard' => null,
					'domain' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			// Tests getting the domain and path from a reference request
			[[
				'foo=1; port="80,8081"; httponly', 'foo=1; port="80,8081"; domain=www.test.com; HttpOnly;', 'foo=1; ; domain=www.test.com; path=/path; port="80,8081"; HttpOnly;'],
				[
					'cookies' => [
						'foo' => 1,
					],
					'data' => [],
					'discard' => null,
					'domain' => 'www.test.com',
					'expires' => null,
					'max_age' => null,
					'path' => '/path',
					'port' => ['80', '8081'],
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => true,
				],
				'http://www.test.com/path/',
			],
			// Some of the following tests are based on http://framework.zend.com/svn/framework/standard/trunk/tests/Zend/Http/CookieTest.php
			[
				'justacookie=foo; domain=example.com',
				[
					'cookies' => [
						'justacookie' => 'foo',
					],
					'domain' => 'example.com',
					'data' => [],
					'discard' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			[
				'expires=tomorrow; secure; path=/Space Out/; expires=Tue, 21-Nov-2006 08:33:44 GMT; domain=.example.com',
				[
					'cookies' => [
						'expires' => 'tomorrow',
					],
					'domain' => '.example.com',
					'path' => '/Space Out/',
					'expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
					'data' => [],
					'discard' => null,
					'port' => null,
					'secure' => true,
					'version' => null,
					'max_age' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			[
				'domain=unittests; expires=Tue, 21-Nov-2006 08:33:44 GMT; domain=example.com; path=/some value/',
				[
					'cookies' => [
						'domain' => 'unittests',
					],
					'domain' => 'example.com',
					'path' => '/some value/',
					'expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
					'secure' => false,
					'data' => [],
					'discard' => null,
					'max_age' => null,
					'port' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			[
				'path=indexAction; path=/; domain=.foo.com; expires=Tue, 21-Nov-2006 08:33:44 GMT',
				[
					'cookies' => [
						'path' => 'indexAction',
					],
					'domain' => '.foo.com',
					'path' => '/',
					'expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
					'secure' => false,
					'data' => [],
					'discard' => null,
					'max_age' => null,
					'port' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			[
				'secure=sha1; secure; SECURE; domain=some.really.deep.domain.com; version=1; Max-Age=86400',
				[
					'cookies' => [
						'secure' => 'sha1',
					],
					'domain' => 'some.really.deep.domain.com',
					'path' => '/',
					'secure' => true,
					'data' => [],
					'discard' => null,
					'expires' => time() + 86400,
					'max_age' => 86400,
					'port' => null,
					'version' => 1,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			[
				'PHPSESSID=123456789+abcd%2Cef; secure; discard; domain=.localdomain; path=/foo/baz; expires=Tue, 21-Nov-2006 08:33:44 GMT;',
				[
					'cookies' => [
						'PHPSESSID' => '123456789+abcd%2Cef',
					],
					'domain' => '.localdomain',
					'path' => '/foo/baz',
					'expires' => 'Tue, 21-Nov-2006 08:33:44 GMT',
					'secure' => true,
					'data' => [],
					'discard' => true,
					'max_age' => null,
					'port' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
			],
			// rfc6265#section-5.1.4
			[
				'cookie=value',
				[
					'cookies' => [
						'cookie' => 'value',
					],
					'domain' => 'example.com',
					'data' => [],
					'discard' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/some/path',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
				'http://example.com/some/path/test.html',
			],
			[
				'empty=path',
				[
					'cookies' => [
						'empty' => 'path',
					],
					'domain' => 'example.com',
					'data' => [],
					'discard' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
				'http://example.com/test.html',
			],
			[
				'baz=qux',
				[
					'cookies' => [
						'baz' => 'qux',
					],
					'domain' => 'example.com',
					'data' => [],
					'discard' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
				'http://example.com?query=here',
			],
			[
				'test=noSlashPath; path=someString',
				[
					'cookies' => [
						'test' => 'noSlashPath',
					],
					'domain' => 'example.com',
					'data' => [],
					'discard' => null,
					'expires' => null,
					'max_age' => null,
					'path' => '/real/path',
					'port' => null,
					'secure' => null,
					'version' => null,
					'comment' => null,
					'comment_url' => null,
					'http_only' => false,
				],
				'http://example.com/real/path/',
			],
		];
	}

	/**
	 * @dataProvider cookieParserDataProvider
	 */
	public function testParseCookie($cookie, $parsed, $url = null) {
		$c = $this->cookieParserClass;
		$parser = new $c();

		$request = null;
		if ($url) {
			$url = Url::factory($url);
			$host = $url->getHost();
			$path = $url->getPath();
		} else {
			$host = '';
			$path = '';
		}

		foreach ((array) $cookie as $c) {
			$p = $parser->parseCookie($c, $host, $path);

			// Remove expires values from the assertion if they are relatively equal by allowing a 5 minute difference
			if ($p['expires'] != $parsed['expires']) {
				if (abs($p['expires'] - $parsed['expires']) < 300) {
					unset($p['expires']);
					unset($parsed['expires']);
				}
			}

			if (is_array($parsed)) {
				foreach ($parsed as $key => $value) {
					$this->assertEquals($parsed[$key], $p[$key], 'Comparing ' . $key . ' ' . var_export($value, true) . ' : ' . var_export($parsed, true) . ' | ' . var_export($p, true));
				}

				foreach ($p as $key => $value) {
					$this->assertEquals($p[$key], $parsed[$key], 'Comparing ' . $key . ' ' . var_export($value, true) . ' : ' . var_export($parsed, true) . ' | ' . var_export($p, true));
				}
			} else {
				$this->assertEquals($parsed, $p);
			}
		}
	}
}
