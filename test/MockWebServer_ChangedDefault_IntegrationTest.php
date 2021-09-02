<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\Responses\NotFoundResponse;
use PHPUnit\Framework\TestCase;

class MockWebServer_ChangedDefault_IntegrationTest extends TestCase {

	public function testChangingDefaultResponse() {
		$server = new MockWebServer;
		$server->start();

		$server->setResponseOfPath('funk', new Response('fresh'));
		$path = $server->getUrlOfResponse(new Response('fries'));

		$content = file_get_contents($server->getServerRoot() . '/PageDoesNotExist');
		$result  = json_decode($content, true);
		$this->assertNotFalse(stripos($http_response_header[0], '200 OK', true) );
		$this->assertSame('/PageDoesNotExist', $result['PARSED_REQUEST_URI']['path']);

		// try with a 404
		$server->setDefaultResponse(new NotFoundResponse);

		$content = file_get_contents($server->getServerRoot() . '/PageDoesNotExist', false, stream_context_create([
			'http' => [ 'ignore_errors' => true ], // allow reading 404s
		]));

		$this->assertNotFalse(stripos($http_response_header[0], '404 Not Found', true));
		$this->assertSame("VND.DonatStudios.MockWebServer: Resource '/PageDoesNotExist' not found!\n", $content);

		// try with a custom response
		$server->setDefaultResponse(new Response('cool beans'));
		$content = file_get_contents($server->getServerRoot() . '/BadUrlBadTime');
		$this->assertSame('cool beans', $content);

		// ensure non-404-ing pages countinue to work as expected
		$content = file_get_contents($server->getServerRoot() . '/funk');
		$this->assertSame('fresh', $content);

		$content = file_get_contents($path);
		$this->assertSame('fries', $content);
	}

}
