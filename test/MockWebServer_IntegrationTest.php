<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;

class MockWebServer_IntegrationTest extends PHPUnit_Framework_TestCase {

	/** @var MockWebServer */
	protected static $server;

	public static function setUpBeforeClass() {
		self::$server = new MockWebServer;
		self::$server->start();
	}

	public function testBasic() {
		$url     = self::$server->getServerRoot() . '/endpoint?get=foobar';
		$content = file_get_contents($url);

		$this->assertJsonStringEqualsJsonString($content, sprintf(<<<EOF
{
    "_GET": {
        "get": "foobar"
    },
    "_POST": [],
    "_FILES": [],
    "_COOKIE": [],
    "HEADERS": {
        "Host": "127.0.0.1:%d",
        "Connection": "close"
    },
    "METHOD": "GET",
    "INPUT": "",
    "PARSED_INPUT": [],
    "REQUEST_URI": "\/endpoint?get=foobar",
    "PARSED_REQUEST_URI": {
        "path": "\/endpoint",
        "query": "get=foobar"
    }
}

EOF
			, self::$server->getPort()));
	}

	public function testSimple() {
		// We define the servers response to requests of the /definedPath endpoint
		$url = self::$server->setResponseOfPath(
			'/definedPath',
			new Response(
				'This is our http body response',
				[ 'X-Foo-Bar' => 'BazBazBaz' ],
				200
			)
		);

		$content = file_get_contents($url);
		$this->assertContains('X-Foo-Bar: BazBazBaz', $http_response_header);
		$this->assertEquals("This is our http body response", $content);
	}

	public function testMulti() {
		$url = self::$server->getUrlOfResponse(
			new ResponseStack(
				new Response("Response One", [ 'X-Boop-Bat' => 'Sauce' ], 500),
				new Response("Response Two", [ 'X-Slaw-Dawg: FranCran' ], 400)
			)
		);

		$ctx = stream_context_create([ 'http' => [ 'ignore_errors' => true ] ]);

		echo "Requesting: $url\n\n";

		$content = file_get_contents($url, false, $ctx);
		$this->assertContains('HTTP/1.0 500 Internal Server Error', $http_response_header);
		$this->assertContains('X-Boop-Bat: Sauce', $http_response_header);
		$this->assertEquals("Response One", $content);

		$content = file_get_contents($url, false, $ctx);
		$this->assertContains('HTTP/1.0 400 Bad Request', $http_response_header);
		$this->assertContains('X-Slaw-Dawg: FranCran', $http_response_header);
		$this->assertEquals("Response Two", $content);

		// this is expected to fail as we only have two responses in said stack
		$content = file_get_contents($url, false, $ctx);
		$this->assertContains('HTTP/1.0 404 Not Found', $http_response_header);
		$this->assertEquals("Past the end of the Response Stack", $content);
	}

}
