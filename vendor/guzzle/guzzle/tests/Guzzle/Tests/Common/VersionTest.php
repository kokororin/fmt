<?php

namespace Guzzle\Tests\Common;

use Guzzle\Common\Version;

/**
 * @covers Guzzle\Common\Version
 */
class VersionTest extends \Guzzle\Tests\GuzzleTestCase {
	public function testCanSilenceWarnings() {
		Version::$emitWarnings = false;
		Version::warn('testing!');
		Version::$emitWarnings = true;
	}

	/**
	 * @expectedException \PHPUnit_Framework_Error_Deprecated
	 */
	public function testEmitsWarnings() {
		Version::$emitWarnings = true;
		Version::warn('testing!');
	}
}
