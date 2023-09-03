<?php

namespace Test\Integration;

use donatj\MockWebServer\InternalServer;
use donatj\MockWebServer\RequestInfo;
use PHPUnit\Framework\TestCase;

class InternalServer_IntegrationTest extends TestCase {

	public function testInternalServer_DefaultResponse() : void {
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(microtime(true));
		mkdir($tmp);

		InternalServer::incrementRequestCounter($tmp, 0);

		$headers = [];
		$header  = static function ( $header ) use ( &$headers ) {
			$headers[] = $header;
		};

		$r = new RequestInfo(
			[
				'REQUEST_METHOD' => 'GET',
				'REQUEST_URI'    => '/test?foo=bar&baz[]=qux&baz[]=quux',
			],
			[ 'foo' => 1 ],
			[ 'baz' => 2 ],
			[],
			[],
			[],
			''
		);

		$server = new InternalServer($tmp, $r, $header);

		ob_start();
		$server();
		$contents = ob_get_clean();

		$this->assertJson($contents);
		$body = json_decode($contents, true);

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

}
