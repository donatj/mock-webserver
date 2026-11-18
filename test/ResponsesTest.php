<?php

namespace Test;

use donatj\MockWebServer\Responses\DefaultResponse;
use donatj\MockWebServer\Responses\NotFoundResponse;
use PHPUnit\Framework\TestCase;

class ResponsesTest extends TestCase {

	/**
	 * @dataProvider responseClassProvider
	 */
	public function testGetRefIsUniquePerInstance( string $responseClass ) : void {
		$first = new $responseClass;

		$this->assertSame($first->getRef(), $first->getRef(), 'A response ref must remain stable');
		$this->assertNotSame(
			$first->getRef(),
			(new $responseClass)->getRef(),
			'Independent responses must not share storage'
		);
	}

	public function responseClassProvider() : array {
		return [
			[ DefaultResponse::class ],
			[ NotFoundResponse::class ],
		];
	}

}
