<?php

use donatj\MockWebServer\MockWebServer;

class MockWebServer_GetRequestByOffset_IntegrationTest extends \PHPUnit\Framework\TestCase {

	public function testGetRequestByOffset() {
		$server = new MockWebServer;
		$server->start();

		for( $i = 0; $i <= 80; $i++ ) {
			$link = $server->getServerRoot() . '/link' . $i;
			file_get_contents($link);
		}

		for( $i = 0; $i <= 80; $i++ ) {
			$this->assertSame('/link' . $i, $server->getRequestByOffset($i)->getRequestUri(),
				"test positive offset alignment");
		}

		for( $i = 0; $i <= 80; $i++ ) {
			$this->assertSame('/link' . $i, $server->getRequestByOffset(-81 + $i)->getRequestUri(),
				"test negative offset alignment");
		}
	}

}
