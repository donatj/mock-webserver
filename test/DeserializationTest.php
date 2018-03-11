<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;

class DeserializationTest extends PHPUnit_Framework_TestCase {

	/** @var MockWebServer */
	protected static $_server;

	public static function setUpBeforeClass() {
		self::$_server = new MockWebServer;
		self::$_server->start();
	}

	public function testDeserializationSingle() {
        $json = <<<EOF
{
    "/hello/world": {
        "GET": {
            "body": "Hello, world!",
            "headers": {
                "X-Hello": "World"
            }
        }
    }
}
EOF;

        self::$_server->load($json);

		$url     = self::$_server->getServerRoot() . '/hello/world';
		$content = file_get_contents($url);

        $this->assertEquals("Hello, world!", $content);
        $this->assertContains('X-Hello: World', $http_response_header);
	}

    public function testDeserializationMulti() {
        $json = <<<EOF
{
    "/hello/world": {
        "GET": [
            {
                "body": "Hello, world!",
                "headers": {
                    "X-Hello": "World"
                }
            },
            {
                "body": "World, hello!",
                "headers": {
                    "X-World": "Hello"
                }
            }
        ]
    }
}
EOF;

        self::$_server->load($json);

        $url     = self::$_server->getServerRoot() . '/hello/world';
        $content = file_get_contents($url);

        $this->assertEquals("Hello, world!", $content);
        $this->assertContains('X-Hello: World', $http_response_header);

        $url     = self::$_server->getServerRoot() . '/hello/world';
        $content = file_get_contents($url);

        $this->assertEquals("World, hello!", $content);
        $this->assertContains('X-World: Hello', $http_response_header);
    }
}
