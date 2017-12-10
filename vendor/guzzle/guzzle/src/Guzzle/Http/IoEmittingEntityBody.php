<?php

namespace Guzzle\Http;

use Guzzle\Common\Event;
use Guzzle\Common\HasDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * EntityBody decorator that emits events for read and write methods
 */
class IoEmittingEntityBody extends AbstractEntityBodyDecorator implements HasDispatcherInterface {
	/** @var EventDispatcherInterface */
	protected $eventDispatcher;

	/**
	 * {@inheritdoc}
	 * @codeCoverageIgnore
	 */
	public function addSubscriber(EventSubscriberInterface $subscriber) {
		$this->getEventDispatcher()->addSubscriber($subscriber);

		return $this;
	}

	public function dispatch($eventName, array $context = []) {
		return $this->getEventDispatcher()->dispatch($eventName, new Event($context));
	}

	public static function getAllEvents() {
		return ['body.read', 'body.write'];
	}

	public function getEventDispatcher() {
		if (!$this->eventDispatcher) {
			$this->eventDispatcher = new EventDispatcher();
		}

		return $this->eventDispatcher;
	}

	public function read($length) {
		$event = [
			'body' => $this,
			'length' => $length,
			'read' => $this->body->read($length),
		];
		$this->dispatch('body.read', $event);

		return $event['read'];
	}

	/**
	 * {@inheritdoc}
	 * @codeCoverageIgnore
	 */
	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher) {
		$this->eventDispatcher = $eventDispatcher;

		return $this;
	}

	public function write($string) {
		$event = [
			'body' => $this,
			'write' => $string,
			'result' => $this->body->write($string),
		];
		$this->dispatch('body.write', $event);

		return $event['result'];
	}
}
