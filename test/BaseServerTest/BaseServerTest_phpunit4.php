<?php

use donatj\MockWebServer\MockWebServer;
use PHPUnit\Framework\TestCase;

abstract class BaseServerTest extends TestCase {

	/** @var MockWebServer */
	protected static $server;

	public static function setUpBeforeClass() {
		self::$server = new MockWebServer;
		self::$server->start();
	}

}
