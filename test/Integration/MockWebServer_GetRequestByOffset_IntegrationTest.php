<?php

namespace Test\Integration;

use donatj\MockWebServer\MockWebServer;
use PHPUnit\Framework\TestCase;

class MockWebServer_GetRequestByOffset_IntegrationTest extends TestCase {

	public function testGetRequestByOffset() : void {
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
