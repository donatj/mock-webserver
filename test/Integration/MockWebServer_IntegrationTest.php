<?php

namespace Test\Integration;

use donatj\MockWebServer\DelayedResponse;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseByMethod;
use donatj\MockWebServer\ResponseStack;
use PHPUnit\Framework\TestCase;

class MockWebServer_IntegrationTest extends TestCase {

	/** @var MockWebServer */
	protected static $server;

	public static function setUpBeforeClass() : void {
		self::$server = new MockWebServer;
		self::$server->start();
	}

	public function testBasic() : void {
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

		$lastReq = self::$server->getLastRequest()->jsonSerialize();
		foreach( $body as $key => $val ) {
			if( $key === 'HEADERS' ) {
				// This is the same horrible connection hack as above. Fix in time.
				unset($lastReq[$key]['Connection']);
			}

			$this->assertSame($lastReq[$key], $val);
		}
	}

	public function testSimple() : void {
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

	public function testMulti() : void {
		$url = self::$server->getUrlOfResponse(
			new ResponseStack(
				new Response("Response One", [ 'X-Boop-Bat' => 'Sauce' ], 500),
				new Response("Response Two", [ 'X-Slaw-Dawg: FranCran' ], 400)
			)
		);

		$ctx = stream_context_create([ 'http' => [ 'ignore_errors' => true ] ]);

		$content = file_get_contents($url, false, $ctx);

		if( !(
			in_array('HTTP/1.0 500 Internal Server Error', $http_response_header, true)
			|| in_array('HTTP/1.1 500 Internal Server Error', $http_response_header, true))
		) {
			$this->fail('must contain 500 Internal Server Error');
		}

		$this->assertContains('X-Boop-Bat: Sauce', $http_response_header);
		$this->assertEquals("Response One", $content);

		$content = file_get_contents($url, false, $ctx);
		if( !(
			in_array('HTTP/1.0 400 Bad Request', $http_response_header, true)
			|| in_array('HTTP/1.1 400 Bad Request', $http_response_header, true))
		) {
			$this->fail('must contain 400 Bad Request');
		}

		$this->assertContains('X-Slaw-Dawg: FranCran', $http_response_header);
		$this->assertEquals("Response Two", $content);

		// this is expected to fail as we only have two responses in said stack
		$content = file_get_contents($url, false, $ctx);
		if( !(
			in_array('HTTP/1.0 404 Not Found', $http_response_header, true)
			|| in_array('HTTP/1.1 404 Not Found', $http_response_header, true))
		) {
			$this->fail('must contain 404 Not Found');
		}

		$this->assertEquals("Past the end of the ResponseStack", $content);
	}

	public function testHttpMethods() : void {
		$methods = [
			ResponseByMethod::METHOD_GET,
			ResponseByMethod::METHOD_POST,
			ResponseByMethod::METHOD_PUT,
			ResponseByMethod::METHOD_PATCH,
			ResponseByMethod::METHOD_DELETE,
			ResponseByMethod::METHOD_HEAD,
			ResponseByMethod::METHOD_OPTIONS,
			ResponseByMethod::METHOD_TRACE,
		];

		$response = new ResponseByMethod;

		foreach( $methods as $method ) {
			$response->setMethodResponse($method, new Response(
				"This is our http $method body response",
				[ 'X-Foo-Bar' => 'Baz ' . $method ],
				200
			));
		}

		$url = self::$server->setResponseOfPath('/definedPath', $response);

		foreach( $methods as $method ) {
			$context = stream_context_create([ 'http' => [ 'method' => $method ] ]);
			$content = file_get_contents($url, false, $context);

			$this->assertContains('X-Foo-Bar: Baz ' . $method, $http_response_header);

			if( $method !== ResponseByMethod::METHOD_HEAD ) {
				$this->assertEquals("This is our http $method body response", $content);
			}
		}

		$context = stream_context_create([ 'http' => [ 'method' => 'PROPFIND' ] ]);
		$content = @file_get_contents($url, false, $context);

		$this->assertFalse($content);
		$this->assertStringEndsWith('501 Not Implemented', $http_response_header[0]);
	}

	public function testHttpMethods_fallthrough() : void {
		$response = new ResponseByMethod([], new Response('Default Fallthrough', [], 400));

		$url = self::$server->setResponseOfPath('/definedPath', $response);

		$context = stream_context_create([ 'http' => [ 'method' => 'PROPFIND', 'ignore_errors' => true ] ]);
		$content = @file_get_contents($url, false, $context);

		$this->assertSame('Default Fallthrough', $content);
		$this->assertStringEndsWith('400 Bad Request', $http_response_header[0]);
	}

	public function testDelayedResponse() : void {

		$realtimeResponse = new Response(
			'This is our http body response',
			[ 'X-Foo-Bar' => 'BazBazBaz' ],
			200
		);

		$delayedResponse = new DelayedResponse($realtimeResponse, 1000000);

		$this->assertNotSame($realtimeResponse->getRef(), $delayedResponse->getRef(),
			'DelayedResponse should change the ref. If they are the same, using both causes issues.');

		$realtimeUrl = self::$server->setResponseOfPath('/realtimePath', $realtimeResponse);
		$delayedUrl  = self::$server->setResponseOfPath('/delayedPath', $delayedResponse);

		$realtimeStart = microtime(true);
		$content = @file_get_contents($realtimeUrl);
		$this->assertNotFalse($content);

		$delayedStart   = microtime(true);
		$delayedContent = file_get_contents($delayedUrl);

		$end = microtime(true);

		$this->assertGreaterThan(.9, ($end - $delayedStart) - ($delayedStart - $realtimeStart), 'Delayed response should take ~1 seconds longer than realtime response');

		$this->assertEquals('This is our http body response', $delayedContent);
		$this->assertContains('X-Foo-Bar: BazBazBaz', $http_response_header);
	}

	public function testDelayedMultiResponse() : void {
		$multi = new ResponseStack(
			new Response('Response One', [ 'X-Boop-Bat' => 'Sauce' ], 200),
			new Response('Response Two', [ 'X-Slaw-Dawg: FranCran' ], 200)
		);

		$delayed = new DelayedResponse($multi, 1000000);

		$path = self::$server->setResponseOfPath('/delayedMultiPath', $delayed);

		$start      = microtime(true);
		$contentOne = file_get_contents($path);
		$this->assertSame($contentOne, 'Response One');
		$this->assertContains('X-Boop-Bat: Sauce', $http_response_header);
		$this->assertGreaterThan(.9, microtime(true) - $start, 'Delayed response should take ~1 seconds longer than realtime response');

		$start      = microtime(true);
		$contentTwo = file_get_contents($path);
		$this->assertSame($contentTwo, 'Response Two');
		$this->assertContains('X-Slaw-Dawg: FranCran', $http_response_header);
		$this->assertGreaterThan(.9, microtime(true) - $start, 'Delayed response should take ~1 seconds longer than realtime response');
	}

	public function testMultiResponseWithPartialDelay() : void {
		$multi = new ResponseStack(
			new Response('Response One', [ 'X-Boop-Bat' => 'Sauce' ], 200),
			new DelayedResponse(new Response('Response Two', [ 'X-Slaw-Dawg: FranCran' ], 200), 1000000)
		);

		$path = self::$server->setResponseOfPath('/delayedMultiPath', $multi);

		$start      = microtime(true);
		$contentOne = file_get_contents($path);
		$this->assertSame($contentOne, 'Response One');
		$this->assertContains('X-Boop-Bat: Sauce', $http_response_header);
		$this->assertLessThan(.2, microtime(true) - $start, 'Delayed response should take less than 200ms');

		$start      = microtime(true);
		$contentTwo = file_get_contents($path);
		$this->assertSame($contentTwo, 'Response Two');
		$this->assertContains('X-Slaw-Dawg: FranCran', $http_response_header);
		$this->assertGreaterThan(.9, microtime(true) - $start, 'Delayed response should take ~1 seconds longer than realtime response');
	}

	/**
	 * Regression Test - Was a problem in 1.0.0-beta.2
	 */
	public function testEmptySingle() : void {
		$url = self::$server->getUrlOfResponse(new Response(''));
		$this->assertSame('', file_get_contents($url));
	}

	public function testBinaryResponse() : void {
		$response = new Response(
			gzencode('This is our http body response'),
			[ 'Content-Encoding: gzip' ],
			200
		);

		$url = self::$server->setResponseOfPath('/', $response);
		$content = @file_get_contents($url);

		$this->assertNotFalse($content);
		$this->assertSame('This is our http body response', gzdecode($content));
		$this->assertContains('Content-Encoding: gzip', $http_response_header);
	}

	/**
	 * @dataProvider requestInfoProvider
	 */
	public function testRequestInfo(
		$method,
		$uri,
		$respBody,
		$reqBody,
		array $headers,
		$status,
		$query,
		array $expectedCookies,
		array $serverVars
	) {
		$url = self::$server->setResponseOfPath($uri, new Response($respBody, $headers, $status));

		// Get cURL resource
		$ch = curl_init();

		// Set url
		curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$xheaders = [];
		foreach( $headers as $hkey => $hval ) {
			$xheaders[] = "{$hkey}: $hval";
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $xheaders);
		// Create body

		if( is_array($reqBody) ) {
			$encReqBody = http_build_query($reqBody);
		} else {
			$encReqBody = $reqBody ?: '';
		}

		if( $encReqBody ) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encReqBody);
		}

		// Send the request & save response to $resp
		$resp = curl_exec($ch);
		$this->assertNotEmpty($resp, "response body is empty, request failed");

		$this->assertSame($status, curl_getinfo($ch, CURLINFO_HTTP_CODE));

		// Close request to clear up some resources
		curl_close($ch);

		$request = self::$server->getLastRequest();

		$this->assertSame($uri . '?' . $query, $request->getRequestUri());
		$this->assertSame([ 'path' => $uri, 'query' => ltrim($query, '?') ], $request->getParsedUri());
		$this->assertContains(self::$server->getHost() . ':' . self::$server->getPort(),
			$request->getHeaders());

		$reqHeaders = $request->getHeaders();
		foreach( $headers as $hkey => $hval ) {
			$this->assertSame($reqHeaders[$hkey], $hval);
		}

		$this->assertSame($query, http_build_query($request->getGet()));
		$this->assertSame($method, $request->getRequestMethod());

		$this->assertSame($expectedCookies, $request->getCookie());

		$this->assertSame($encReqBody, $request->getInput());

		parse_str($encReqBody, $decReqBody);
		$this->assertSame($decReqBody, $request->getParsedInput());
		if( $method === 'POST' ) {
			$this->assertSame($decReqBody, $request->getPost());
		}

		$server = $request->getServer();

		$this->assertEquals(self::$server->getHost(), $server['SERVER_NAME']);
		$this->assertEquals(self::$server->getPort(), $server['SERVER_PORT']);

		foreach( $serverVars as $sKey => $sVal ) {
			$this->assertSame($server[$sKey], $sVal);
		}
	}

	public function requestInfoProvider() : array {
		return [
			[
				'GET',
				'/requestInfoPath',
				'This is our http body response',
				null,
				[ 'X-Foo-Bar' => 'BazBazBaz', 'Accept' => 'Juice' ],
				200,
				'foo=bar',
				[],
				[ 'HTTP_ACCEPT' => 'Juice', 'QUERY_STRING' => 'foo=bar' ],
			],
			[
				'POST',
				'/requestInfoPath',
				'This is my POST response',
				[ 'a' => 1 ],
				[ 'X-Boo-Bop' => 'Beep Boop', 'Cookie' => 'juice=mango' ],
				301,
				'x=1',
				[
					'juice' => 'mango',
				],
				[ 'REQUEST_METHOD' => 'POST', 'QUERY_STRING' => 'x=1' ],
			],
			[
				'PUT',
				'/put/path/90210',
				'Put put put',
				[ 'a' => 1 ],
				[ 'X-Boo-Bop' => 'Beep Boop', 'Cookie' => 'a=b; c=d; e=f; what="soup"' ],
				301,
				'x=1',
				[
					'a'    => 'b',
					'c'    => 'd',
					'e'    => 'f',
					'what' => '"soup"',
				],
				[ 'REQUEST_METHOD' => 'PUT', 'QUERY_STRING' => 'x=1' ],
			],
		];
	}

	public function testStartStopServer() : void {
		$server = new MockWebServer;

		$server->start();
		$this->assertTrue($server->isRunning());

		$server->stop();
		$this->assertFalse($server->isRunning());
	}

}
