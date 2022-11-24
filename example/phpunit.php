<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

class ExampleTest extends PHPUnit\Framework\TestCase {

	/** @var MockWebServer */
	protected static $server;

	public static function setUpBeforeClass() : void {
		self::$server = new MockWebServer;
		self::$server->start();
	}

	public function testGetParams() : void {
		$result  = file_get_contents(self::$server->getServerRoot() . '/autoEndpoint?foo=bar');
		$decoded = json_decode($result, true);
		$this->assertSame('bar', $decoded['_GET']['foo']);
	}

	public function testGetSetPath() : void {
		// $url = http://127.0.0.1:8123/definedEndPoint
		$url    = self::$server->setResponseOfPath('/definedEndPoint', new Response('foo bar content'));
		$result = file_get_contents($url);
		$this->assertSame('foo bar content', $result);
	}

	public static function tearDownAfterClass() : void {
		// stopping the web server during tear down allows us to reuse the port for later tests
		self::$server->stop();
	}

}
