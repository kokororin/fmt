<?php

namespace Guzzle\Log;

/**
 * Stores all log messages in an array
 */
class ArrayLogAdapter implements LogAdapterInterface {
	protected $logs = [];

	/**
	 * Clears logged entries
	 */
	public function clearLogs() {
		$this->logs = [];
	}

	/**
	 * Get logged entries
	 *
	 * @return array
	 */
	public function getLogs() {
		return $this->logs;
	}

	public function log($message, $priority = LOG_INFO, $extras = []) {
		$this->logs[] = ['message' => $message, 'priority' => $priority, 'extras' => $extras];
	}
}
