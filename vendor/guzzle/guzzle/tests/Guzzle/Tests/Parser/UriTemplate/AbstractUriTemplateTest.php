<?php

namespace Guzzle\Tests\Parsers\UriTemplate;

abstract class AbstractUriTemplateTest extends \Guzzle\Tests\GuzzleTestCase {
	/**
	 * @return array
	 */
	public function templateProvider() {
		$t = [];
		$params = [
			'var' => 'value',
			'hello' => 'Hello World!',
			'empty' => '',
			'path' => '/foo/bar',
			'x' => '1024',
			'y' => '768',
			'null' => null,
			'list' => ['red', 'green', 'blue'],
			'keys' => [
				'semi' => ';',
				'dot' => '.',
				'comma' => ',',
			],
			'empty_keys' => [],
		];

		return array_map(function ($t) use ($params) {
			$t[] = $params;
			return $t;
		}, [
			['foo', 'foo'],
			['{var}', 'value'],
			['{hello}', 'Hello%20World%21'],
			['{+var}', 'value'],
			['{+hello}', 'Hello%20World!'],
			['{+path}/here', '/foo/bar/here'],
			['here?ref={+path}', 'here?ref=/foo/bar'],
			['X{#var}', 'X#value'],
			['X{#hello}', 'X#Hello%20World!'],
			['map?{x,y}', 'map?1024,768'],
			['{x,hello,y}', '1024,Hello%20World%21,768'],
			['{+x,hello,y}', '1024,Hello%20World!,768'],
			['{+path,x}/here', '/foo/bar,1024/here'],
			['{#x,hello,y}', '#1024,Hello%20World!,768'],
			['{#path,x}/here', '#/foo/bar,1024/here'],
			['X{.var}', 'X.value'],
			['X{.x,y}', 'X.1024.768'],
			['{/var}', '/value'],
			['{/var,x}/here', '/value/1024/here'],
			['{;x,y}', ';x=1024;y=768'],
			['{;x,y,empty}', ';x=1024;y=768;empty'],
			['{?x,y}', '?x=1024&y=768'],
			['{?x,y,empty}', '?x=1024&y=768&empty='],
			['?fixed=yes{&x}', '?fixed=yes&x=1024'],
			['{&x,y,empty}', '&x=1024&y=768&empty='],
			['{var:3}', 'val'],
			['{var:30}', 'value'],
			['{list}', 'red,green,blue'],
			['{list*}', 'red,green,blue'],
			['{keys}', 'semi,%3B,dot,.,comma,%2C'],
			['{keys*}', 'semi=%3B,dot=.,comma=%2C'],
			['{+path:6}/here', '/foo/b/here'],
			['{+list}', 'red,green,blue'],
			['{+list*}', 'red,green,blue'],
			['{+keys}', 'semi,;,dot,.,comma,,'],
			['{+keys*}', 'semi=;,dot=.,comma=,'],
			['{#path:6}/here', '#/foo/b/here'],
			['{#list}', '#red,green,blue'],
			['{#list*}', '#red,green,blue'],
			['{#keys}', '#semi,;,dot,.,comma,,'],
			['{#keys*}', '#semi=;,dot=.,comma=,'],
			['X{.var:3}', 'X.val'],
			['X{.list}', 'X.red,green,blue'],
			['X{.list*}', 'X.red.green.blue'],
			['X{.keys}', 'X.semi,%3B,dot,.,comma,%2C'],
			['X{.keys*}', 'X.semi=%3B.dot=..comma=%2C'],
			['{/var:1,var}', '/v/value'],
			['{/list}', '/red,green,blue'],
			['{/list*}', '/red/green/blue'],
			['{/list*,path:4}', '/red/green/blue/%2Ffoo'],
			['{/keys}', '/semi,%3B,dot,.,comma,%2C'],
			['{/keys*}', '/semi=%3B/dot=./comma=%2C'],
			['{;hello:5}', ';hello=Hello'],
			['{;list}', ';list=red,green,blue'],
			['{;list*}', ';list=red;list=green;list=blue'],
			['{;keys}', ';keys=semi,%3B,dot,.,comma,%2C'],
			['{;keys*}', ';semi=%3B;dot=.;comma=%2C'],
			['{?var:3}', '?var=val'],
			['{?list}', '?list=red,green,blue'],
			['{?list*}', '?list=red&list=green&list=blue'],
			['{?keys}', '?keys=semi,%3B,dot,.,comma,%2C'],
			['{?keys*}', '?semi=%3B&dot=.&comma=%2C'],
			['{&var:3}', '&var=val'],
			['{&list}', '&list=red,green,blue'],
			['{&list*}', '&list=red&list=green&list=blue'],
			['{&keys}', '&keys=semi,%3B,dot,.,comma,%2C'],
			['{&keys*}', '&semi=%3B&dot=.&comma=%2C'],
			['{.null}', ''],
			['{.null,var}', '.value'],
			['X{.empty_keys*}', 'X'],
			['X{.empty_keys}', 'X'],
			// Test that missing expansions are skipped
			['test{&missing*}', 'test'],
			// Test that multiple expansions can be set
			['http://{var}/{var:2}{?keys*}', 'http://value/va?semi=%3B&dot=.&comma=%2C'],
			// Test more complex query string stuff
			['http://www.test.com{+path}{?var,keys*}', 'http://www.test.com/foo/bar?var=value&semi=%3B&dot=.&comma=%2C'],
		]);
	}
}
