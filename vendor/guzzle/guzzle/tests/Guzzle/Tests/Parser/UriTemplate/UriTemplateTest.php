<?php

namespace Guzzle\Tests\Parsers\UriTemplate;

use Guzzle\Parser\UriTemplate\UriTemplate;

/**
 * @covers Guzzle\Parser\UriTemplate\UriTemplate
 */
class UriTemplateTest extends AbstractUriTemplateTest {
	public function expressionProvider() {
		return [
			[
				'{+var*}', [
					'operator' => '+',
					'values' => [
						['value' => 'var', 'modifier' => '*'],
					],
				],
			],
			[
				'{?keys,var,val}', [
					'operator' => '?',
					'values' => [
						['value' => 'keys', 'modifier' => ''],
						['value' => 'var', 'modifier' => ''],
						['value' => 'val', 'modifier' => ''],
					],
				],
			],
			[
				'{+x,hello,y}', [
					'operator' => '+',
					'values' => [
						['value' => 'x', 'modifier' => ''],
						['value' => 'hello', 'modifier' => ''],
						['value' => 'y', 'modifier' => ''],
					],
				],
			],
		];
	}

	/**
	 * @ticket https://github.com/guzzle/guzzle/issues/90
	 */
	public function testAllowsNestedArrayExpansion() {
		$template = new UriTemplate();

		$result = $template->expand('http://example.com{+path}{/segments}{?query,data*,foo*}', [
			'path' => '/foo/bar',
			'segments' => ['one', 'two'],
			'query' => 'test',
			'data' => [
				'more' => ['fun', 'ice cream'],
			],
			'foo' => [
				'baz' => [
					'bar' => 'fizz',
					'test' => 'buzz',
				],
				'bam' => 'boo',
			],
		]);

		$this->assertEquals('http://example.com/foo/bar/one,two?query=test&more%5B0%5D=fun&more%5B1%5D=ice%20cream&baz%5Bbar%5D=fizz&baz%5Btest%5D=buzz&bam=boo', $result);
	}

	/**
	 * @dataProvider templateProvider
	 */
	public function testExpandsUriTemplates($template, $expansion, $params) {
		$uri = new UriTemplate($template);
		$this->assertEquals($expansion, $uri->expand($template, $params));
	}

	/**
	 * @dataProvider expressionProvider
	 */
	public function testParsesExpressions($exp, $data) {
		$template = new UriTemplate($exp);

		// Access the config object
		$class = new \ReflectionClass($template);
		$method = $class->getMethod('parseExpression');
		$method->setAccessible(true);

		$exp = substr($exp, 1, -1);
		$this->assertEquals($data, $method->invokeArgs($template, [$exp]));
	}

	/**
	 * @ticket https://github.com/guzzle/guzzle/issues/426
	 */
	public function testSetRegex() {
		$template = new UriTemplate();
		$template->setRegex('/\<\$(.+)\>/');
		$this->assertSame('/foo', $template->expand('/<$a>', ['a' => 'foo']));
	}
}
