<?php

namespace Guzzle\Http;

use Guzzle\Stream\Stream;

/**
 * Abstract decorator used to wrap entity bodies
 */
class AbstractEntityBodyDecorator implements EntityBodyInterface {
	/** @var EntityBodyInterface Decorated entity body */
	protected $body;

	/**
	 * Allow decorators to implement custom methods
	 *
	 * @param string $method Missing method name
	 * @param array  $args   Method arguments
	 *
	 * @return mixed
	 */
	public function __call($method, array $args) {
		return call_user_func_array([$this->body, $method], $args);
	}

	/**
	 * @param EntityBodyInterface $body Entity body to decorate
	 */
	public function __construct(EntityBodyInterface $body) {
		$this->body = $body;
	}

	public function __toString() {
		return (string) $this->body;
	}

	public function close() {
		return $this->body->close();
	}

	public function compress($filter = 'zlib.deflate') {
		return $this->body->compress($filter);
	}

	public function detachStream() {
		$this->body->detachStream();

		return $this;
	}

	/**
	 * Alias of isConsumed()
	 * {@inheritdoc}
	 */
	public function feof() {
		return $this->isConsumed();
	}

	public function ftell() {
		return $this->body->ftell();
	}

	public function getContentEncoding() {
		return $this->body->getContentEncoding();
	}

	public function getContentLength() {
		return $this->getSize();
	}

	public function getContentMd5($rawOutput = false, $base64Encode = false) {
		$hash = Stream::getHash($this, 'md5', $rawOutput);

		return $hash && $base64Encode ? base64_encode($hash) : $hash;
	}

	public function getContentType() {
		return $this->body->getContentType();
	}

	public function getCustomData($key) {
		return $this->body->getCustomData($key);
	}

	public function getMetaData($key = null) {
		return $this->body->getMetaData($key);
	}

	public function getSize() {
		return $this->body->getSize();
	}

	public function getStream() {
		return $this->body->getStream();
	}

	public function getStreamType() {
		return $this->body->getStreamType();
	}

	public function getUri() {
		return $this->body->getUri();
	}

	public function getWrapper() {
		return $this->body->getWrapper();
	}

	public function getWrapperData() {
		return $this->body->getWrapperData();
	}

	public function isConsumed() {
		return $this->body->isConsumed();
	}

	public function isLocal() {
		return $this->body->isLocal();
	}

	public function isReadable() {
		return $this->body->isReadable();
	}

	public function isRepeatable() {
		return $this->isSeekable() && $this->isReadable();
	}

	public function isSeekable() {
		return $this->body->isSeekable();
	}

	public function isWritable() {
		return $this->body->isWritable();
	}

	public function read($length) {
		return $this->body->read($length);
	}

	public function readLine($maxLength = null) {
		return $this->body->readLine($maxLength);
	}

	public function rewind() {
		return $this->body->rewind();
	}

	public function seek($offset, $whence = SEEK_SET) {
		return $this->body->seek($offset, $whence);
	}

	public function setCustomData($key, $value) {
		$this->body->setCustomData($key, $value);

		return $this;
	}

	public function setRewindFunction($callable) {
		$this->body->setRewindFunction($callable);

		return $this;
	}

	public function setSize($size) {
		$this->body->setSize($size);

		return $this;
	}

	public function setStream($stream, $size = 0) {
		$this->body->setStream($stream, $size);

		return $this;
	}

	public function uncompress($filter = 'zlib.inflate') {
		return $this->body->uncompress($filter);
	}

	public function write($string) {
		return $this->body->write($string);
	}
}
