<?php

namespace Guzzle\Common;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class that holds an event dispatcher
 */
class AbstractHasDispatcher implements HasDispatcherInterface {
	/** @var EventDispatcherInterface */
	protected $eventDispatcher;

	public function addSubscriber(EventSubscriberInterface $subscriber) {
		$this->getEventDispatcher()->addSubscriber($subscriber);

		return $this;
	}

	public function dispatch($eventName, array $context = []) {
		return $this->getEventDispatcher()->dispatch($eventName, new Event($context));
	}

	public static function getAllEvents() {
		return [];
	}

	public function getEventDispatcher() {
		if (!$this->eventDispatcher) {
			$this->eventDispatcher = new EventDispatcher();
		}

		return $this->eventDispatcher;
	}

	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher) {
		$this->eventDispatcher = $eventDispatcher;

		return $this;
	}
}
