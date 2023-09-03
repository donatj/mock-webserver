<?php

namespace Test\Integration;

use donatj\MockWebServer\InternalServer;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\TestCase;
use Test\Integration\Mock\ExampleInitializingResponse;

class InternalServer_IntegrationTest extends TestCase {

	private function getTempDirectory() : string {
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(microtime(true) . ':' . rand(0, 100000));
		mkdir($tmp);

		InternalServer::incrementRequestCounter($tmp, 0);

		return $tmp;
	}

	private function getRequestInfo(
		string $uri,
		array $GET = [],
		array $POST = [],
		array $HEADERS = [],
		array $FILES = [],
		array $COOKIE = [],
		string $method = 'GET'
	) : RequestInfo {
		return new RequestInfo(
			[
				'REQUEST_METHOD' => $method,
				'REQUEST_URI'    => $uri,
			],
			$GET, $POST, $FILES, $COOKIE, $HEADERS, ''
		);
	}

	public function testInternalServer_DefaultResponse() : void {
		$tmp = $this->getTempDirectory();

		$headers = [];
		$header  = static function ( $header ) use ( &$headers ) {
			$headers[] = $header;
		};

		$statusCode       = null;
		$httpResponseCode = static function ( $code ) use ( &$statusCode ) {
			$statusCode = $code;
		};

		$r = $this->getRequestInfo('/test?foo=bar&baz[]=qux&baz[]=quux', [ 'foo' => 1 ], [ 'baz' => 2 ]);

		$server = new InternalServer($tmp, $r, $header, $httpResponseCode);

		ob_start();
		$server();
		$contents = ob_get_clean();

		$body = json_decode($contents, true);

		$this->assertSame(200, $statusCode);

		$this->assertSame([
			'Content-Type: application/json',
		], $headers);

		$expectedBody = [
			'_GET'               => [
				'foo' => 1,
			],
			'_POST'              => [
				'baz' => 2,
			],
			'_FILES'             => [
			],
			'_COOKIE'            => [
			],
			'HEADERS'            => [
			],
			'METHOD'             => 'GET',
			'INPUT'              => '',
			'PARSED_INPUT'       => [
			],
			'REQUEST_URI'        => '/test?foo=bar&baz[]=qux&baz[]=quux',
			'PARSED_REQUEST_URI' => [
				'path'  => '/test',
				'query' => 'foo=bar&baz[]=qux&baz[]=quux',
			],
		];

		$this->assertSame($expectedBody, $body);
	}

	/**
	 * @dataProvider provideBodyWithContentType
	 */
	public function testInternalServer_CustomResponse( string $body, string $contentType ) : void {
		$tmp = $this->getTempDirectory();

		$headers = [];
		$header  = static function ( $header ) use ( &$headers ) {
			$headers[] = $header;
		};

		$statusCode       = null;
		$httpResponseCode = static function ( $code ) use ( &$statusCode ) {
			$statusCode = $code;
		};

		$response = new Response($body, [ 'Content-Type' => $contentType ], 200);

		$r = $this->getRequestInfo(InternalServer::getPathOfRef($response->getRef()));

		InternalServer::storeResponse($tmp, $response);
		$server = new InternalServer($tmp, $r, $header, $httpResponseCode);

		ob_start();
		$server();
		$contents = ob_get_clean();

		$this->assertSame(200, $statusCode);

		$this->assertSame([
			'Content-Type: ' . $contentType,
		], $headers);

		$this->assertSame($body, $contents);
	}

	public function provideBodyWithContentType() : \Generator {
		yield [ 'Hello World!', 'text/plain; charset=UTF-8' ];
		yield [ '{"foo":"bar"}', 'application/json' ];
		yield [ '<html><body><h1>Test</h1></body></html>', 'text/html' ];
	}

	public function testInternalServer_DefaultResponseFallthrough() : void {
		$tmp = $this->getTempDirectory();

		$headers = [];
		$header  = static function ( $header ) use ( &$headers ) {
			$headers[] = $header;
		};

		$statusCode       = null;
		$httpResponseCode = static function ( $code ) use ( &$statusCode ) {
			$statusCode = $code;
		};

		$response = new Response('Default Response!!!', [ 'Default' => 'Response!' ], 400);

		$r = $this->getRequestInfo('/any/invalid/response');

		InternalServer::storeDefaultResponse($tmp, $response);
		$server = new InternalServer($tmp, $r, $header, $httpResponseCode);

		ob_start();
		$server();
		$contents = ob_get_clean();

		$this->assertSame(400, $statusCode);

		$this->assertSame([
			'Default: Response!',
		], $headers);

		$this->assertSame('Default Response!!!', $contents);
	}

	public function testInternalServer_InitializingResponse() : void {
		$tmp = $this->getTempDirectory();

		$response = new ExampleInitializingResponse;

		$headers = [];
		$header  = static function ( $header ) use ( &$headers ) {
			$headers[] = $header;
		};

		$r = $this->getRequestInfo(InternalServer::getPathOfRef($response->getRef()));

		InternalServer::storeResponse($tmp, $response);
		$server = new InternalServer($tmp, $r, $header, function () { });

		ob_start();
		$server();
		ob_end_clean();

		$this->assertSame([ 'X-Did-Call-Init: YES' ], $headers);
	}

	public function testInternalServer_InvalidRef404() : void {
		$tmp = $this->getTempDirectory();

		$headers = [];
		$header  = static function ( $header ) use ( &$headers ) {
			$headers[] = $header;
		};

		$statusCode       = null;
		$httpResponseCode = static function ( $code ) use ( &$statusCode ) {
			$statusCode = $code;
		};

		$r = $this->getRequestInfo(InternalServer::getPathOfRef(str_repeat('a', 32)));

		$server = new InternalServer($tmp, $r, $header, $httpResponseCode);

		ob_start();
		$server();
		$contents = ob_get_clean();

		$this->assertSame(404, $statusCode);
		$this->assertSame([], $headers);
		$this->assertSame("VND.DonatStudios.MockWebServer: Resource '/VND.DonatStudios.MockWebServer/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' not found!\n", $contents);
	}

}
