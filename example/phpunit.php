<?php

use donatj\MockWebServer\MockWebServer;

class ExampleTest extends PHPUnit_Framework_TestCase {

	/** @var MockWebServer */
	protected static $server;

	public static function setUpBeforeClass() {
		self::$server = new MockWebServer;
		self::$server->start();
	}

	public function testGetParams() {
		$result  = file_get_contents(self::$server->getServerRoot() . '/autoEndpoint?foo=bar');
		$decoded = json_decode($result, true);
		$this->assertSame('bar', $decoded['_GET']['foo']);
	}

	public function testGetSetPath() {
		// $url = http://127.0.0.1:8123/definedEndPoint
		$url    = self::$server->setResponseOfPath('/definedEndPoint', 'foo bar content');
		$result = file_get_contents($url);
		$this->assertSame('foo bar content', $result);
	}

	static function tearDownAfterClass() {
		self::$server->stop(); // tearing down the server after the class lets us reuse the port for other tests
	}

}
