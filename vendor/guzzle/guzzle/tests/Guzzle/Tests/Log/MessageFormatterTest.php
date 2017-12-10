<?php

namespace Guzzle\Tests\Log;

use Guzzle\Http\Client;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Response;
use Guzzle\Log\ClosureLogAdapter;
use Guzzle\Log\MessageFormatter;
use Guzzle\Plugin\Log\LogPlugin;

/**
 * @covers Guzzle\Log\MessageFormatter
 */
class MessageFormatterTest extends \Guzzle\Tests\GuzzleTestCase {
	protected $handle;

	protected $request;

	protected $response;

	public function logProvider() {
		return [
			// Uses the cache for the second time
			['{method} - {method}', 'POST - POST'],
			['{url}', 'http://foo.com?q=test'],
			['{port}', '80'],
			['{resource}', '/?q=test'],
			['{host}', 'foo.com'],
			['{hostname}', gethostname()],
			['{protocol}/{version}', 'HTTP/1.1'],
			['{code} {phrase}', '200 OK'],
			['{req_header_Foo}', ''],
			['{req_header_X-Foo}', 'bar'],
			['{req_header_Authorization}', 'Baz'],
			['{res_header_foo}', ''],
			['{res_header_X-Test}', 'Abc'],
			['{req_body}', 'Hello'],
			['{res_body}', 'Foo'],
			['{curl_stderr}', 'testing'],
			['{curl_error}', 'e'],
			['{curl_code}', '123'],
			['{connect_time}', '123'],
			['{total_time}', '456'],
		];
	}

	public function setUp() {
		$this->request = new EntityEnclosingRequest('POST', 'http://foo.com?q=test', [
			'X-Foo' => 'bar',
			'Authorization' => 'Baz',
		]);
		$this->request->setBody(EntityBody::factory('Hello'));

		$this->response = new Response(200, [
			'X-Test' => 'Abc',
		], 'Foo');

		$this->handle = $this->getMockBuilder('Guzzle\Http\Curl\CurlHandle')
			->disableOriginalConstructor()
			->setMethods(['getError', 'getErrorNo', 'getStderr', 'getInfo'])
			->getMock();

		$this->handle->expects($this->any())
			->method('getError')
			->will($this->returnValue('e'));

		$this->handle->expects($this->any())
			->method('getErrorNo')
			->will($this->returnValue('123'));

		$this->handle->expects($this->any())
			->method('getStderr')
			->will($this->returnValue('testing'));

		$this->handle->expects($this->any())
			->method('getInfo')
			->will($this->returnValueMap([
				[CURLINFO_CONNECT_TIME, '123'],
				[CURLINFO_TOTAL_TIME, '456'],
			]));
	}

	public function testAddsTimestamp() {
		$formatter = new MessageFormatter('{ts}');
		$this->assertNotEmpty($formatter->format($this->request, $this->response));
	}

	/**
	 * @dataProvider logProvider
	 */
	public function testFormatsMessages($template, $output) {
		$formatter = new MessageFormatter($template);
		$this->assertEquals($output, $formatter->format($this->request, $this->response, $this->handle));
	}

	public function testFormatsRequestsAndResponses() {
		$formatter = new MessageFormatter();
		$formatter->setTemplate('{request}{response}');
		$this->assertEquals($this->request . $this->response, $formatter->format($this->request, $this->response));
	}

	public function testInjectsTotalTime() {
		$out = '';
		$formatter = new MessageFormatter('{connect_time}/{total_time}');
		$adapter = new ClosureLogAdapter(function ($m) use (&$out) {$out .= $m;});
		$log = new LogPlugin($adapter, $formatter);
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 2\r\n\r\nHI");
		$client = new Client($this->getServer()->getUrl());
		$client->addSubscriber($log);
		$client->get('/')->send();
		$this->assertNotEquals('/', $out);
	}

	public function testUsesEmptyStringWhenNoHandleAndNoResponse() {
		$formatter = new MessageFormatter('{connect_time}/{total_time}');
		$this->assertEquals('/', $formatter->format($this->request));
	}

	public function testUsesResponseWhenNoHandleAndGettingCurlInformation() {
		$formatter = new MessageFormatter('{connect_time}/{total_time}');
		$response = $this->getMockBuilder('Guzzle\Http\Message\Response')
			->setConstructorArgs([200])
			->setMethods(['getInfo'])
			->getMock();
		$response->expects($this->exactly(2))
			->method('getInfo')
			->will($this->returnValueMap([
				['connect_time', '1'],
				['total_time', '2'],
			]));
		$this->assertEquals('1/2', $formatter->format($this->request, $response));
	}
}
