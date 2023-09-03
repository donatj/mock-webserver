<?php

namespace Test;

use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;
use PHPUnit\Framework\TestCase;

class ResponseStackTest extends TestCase {

	public function testEmpty() : void {
		$mock = $this->getMockBuilder(RequestInfo::class)->disableOriginalConstructor()->getMock();

		$x = new ResponseStack;

		$this->assertSame('Past the end of the ResponseStack', $x->getBody($mock));
		$this->assertSame(404, $x->getStatus($mock));
		$this->assertSame([], $x->getHeaders($mock));
		$this->assertFalse($x->next());
	}

	/**
	 * @dataProvider customResponseProvider
	 */
	public function testCustomPastEndResponse( $body, $headers, $status ) : void {
		$mock = $this->getMockBuilder(RequestInfo::class)->disableOriginalConstructor()->getMock();

		$x = new ResponseStack;
		$x->setPastEndResponse(new Response($body, $headers, $status));

		$this->assertSame($body, $x->getBody($mock));
		$this->assertSame($status, $x->getStatus($mock));
		$this->assertSame($headers, $x->getHeaders($mock));
		$this->assertFalse($x->next());
	}

	public function customResponseProvider() : array {
		return [
			[ 'PastEnd', [ 'HeaderA' => 'BVAL' ], 420 ],
			[ ' Leading and trailing whitespace ', [], 0 ],
		];
	}

}
