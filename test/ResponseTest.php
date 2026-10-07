<?php

namespace Test;

use donatj\MockWebServer\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase {

	public function testGetRefIsUniquePerInstance() : void {
		$first = new Response('response');

		$this->assertSame($first->getRef(), $first->getRef(), 'A response ref must remain stable');
		$this->assertNotSame(
			$first->getRef(),
			(new Response('response'))->getRef(),
			'Independent responses must not share storage'
		);
	}

}
