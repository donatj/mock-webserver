<?php

namespace Test\Regression;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseByMethod;
use donatj\MockWebServer\ResponseStack;
use PHPUnit\Framework\TestCase;

class ResponseByMethod_RegressionTest extends TestCase {

	public function test_forwardNextAsExpected() : void {
		$server = new MockWebServer;
		$path   = $server->setResponseOfPath(
			'/interest-categories/cat-xyz/interests',
			new ResponseByMethod([
				ResponseByMethod::METHOD_GET    => new ResponseStack(
					new Response('get-a'),
					new Response('get-b'),
					new Response('get-c')
				),
				ResponseByMethod::METHOD_DELETE => new ResponseStack(
					new Response('delete-a'),
					new Response('delete-b'),
					new Response('delete-c')
				),
			])
		);

		$server->start();

		$method = function ( string $method ) {
			return stream_context_create([ 'http' => [ 'method' => $method ] ]);
		};

		$this->assertSame('get-a', file_get_contents($path));
		$this->assertSame('delete-a', file_get_contents($path, false, $method('DELETE')));
		$this->assertSame('get-b', file_get_contents($path));
		$this->assertSame('delete-b', file_get_contents($path, false, $method('DELETE')));
		$this->assertSame('delete-c', file_get_contents($path, false, $method('DELETE')));
		$this->assertSame(false, @file_get_contents($path, false, $method('DELETE')));
		$this->assertSame('get-c', file_get_contents($path));
		$this->assertSame(false, @file_get_contents($path));

		$server->stop();
	}

}
