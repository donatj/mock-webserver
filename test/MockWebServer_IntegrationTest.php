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

		// Some versions of PHP send it with file_get_contents, others do not.
		// Might be removable with a context but until I figure that out, terrible hack
		$content = preg_replace('/,\s*"Connection": "close"/', '', $content);

		$body = [
			'_GET'               => [ 'get' => 'foobar', ],
			'_POST'              => [],
			'_FILES'             => [],
			'_COOKIE'            => [],
			'HEADERS'            => [ 'Host' => '127.0.0.1:' . self::$server->getPort(), ],
			'METHOD'             => 'GET',
			'INPUT'              => '',
			'PARSED_INPUT'       => [],
			'REQUEST_URI'        => '/endpoint?get=foobar',
			'PARSED_REQUEST_URI' => [ 'path' => '/endpoint', 'query' => 'get=foobar', ],
		];

		$this->assertJsonStringEqualsJsonString($content, json_encode($body));

		$lastReq = self::$server->getLastRequest();
		foreach( $body as $key => $val ) {
			if( $key == 'HEADERS' ) {
				// This is the same horrible connection hack as above. Fix in time.
				unset($lastReq[$key]['Connection']);
			}
			$this->assertSame($lastReq[$key], $val);
		}
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
		$this->assertEquals("Past the end of the ResponseStack", $content);
	}

	public function testHttpMethods() {
	    $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'TRACE'];

	    foreach ($methods as $method)
	    {
            $url = self::$server->setResponseOfPath(
                '/definedPath',
                new Response(
                    'This is our http body response',
                    ['X-Foo-Bar' => 'Baz'],
                    200
                ),
                $method
            );

            $context = stream_context_create(['http' => ['method'  => $method]]);
            $content = file_get_contents($url, false, $context);

            $this->assertContains('X-Foo-Bar: Baz', $http_response_header);

            if ($method != 'HEAD') {
                $this->assertEquals("This is our http body response", $content);
            }
        }
    }

	/**
	 * Regression Test - Was a problem in 1.0.0-beta.2
	 */
	public function testEmptySingle() {
		$url = self::$server->getUrlOfResponse(new Response(''));
		$this->assertSame('', file_get_contents($url));
	}

}
