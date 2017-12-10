<?php

namespace Guzzle\Tests\Common;

use Guzzle\Common\Event;

/**
 * @covers Guzzle\Common\Event
 */
class EventTest extends \Guzzle\Tests\GuzzleTestCase {
	public function testAllowsParameterInjection() {
		$event = new Event([
			'test' => '123',
		]);
		$this->assertEquals('123', $event['test']);
	}

	public function testConvertsToArray() {
		$this->assertEquals([
			'test' => '123',
			'other' => '456',
			'event' => 'test.notify',
		], $this->getEvent()->toArray());
	}

	public function testImplementsArrayAccess() {
		$event = $this->getEvent();
		$this->assertEquals('123', $event['test']);
		$this->assertNull($event['foobar']);

		$this->assertTrue($event->offsetExists('test'));
		$this->assertFalse($event->offsetExists('foobar'));

		unset($event['test']);
		$this->assertFalse($event->offsetExists('test'));

		$event['test'] = 'new';
		$this->assertEquals('new', $event['test']);
	}

	public function testImplementsIteratorAggregate() {
		$event = $this->getEvent();
		$this->assertInstanceOf('ArrayIterator', $event->getIterator());
	}

	/**
	 * @return Event
	 */
	private function getEvent() {
		return new Event([
			'test' => '123',
			'other' => '456',
			'event' => 'test.notify',
		]);
	}
}
