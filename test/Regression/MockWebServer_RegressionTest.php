<?php

namespace Test\Regression;

use donatj\MockWebServer\MockWebServer;
use PHPUnit\Framework\TestCase;

class MockWebServer_RegressionTest extends TestCase {

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_stopTwiceShouldNotExplode() : void {
		$server = new MockWebServer;
		$server->start();
		$server->stop();
		$server->stop();
	}

}
