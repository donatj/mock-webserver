<?php

use donatj\MockWebServer\ResponseStack;
use PHPUnit\Framework\TestCase;

class ResponseStackTest extends TestCase {

	public function testEmpty() {
		$mock = $this->getMockBuilder('\donatj\MockWebServer\RequestInfo')->disableOriginalConstructor()->getMock();

		$x = new ResponseStack;

		$this->assertSame('Past the end of the ResponseStack', $x->getBody($mock));
		$this->assertSame(404, $x->getStatus($mock));
		$this->assertSame([], $x->getHeaders($mock));
		$this->assertSame(false, $x->next());
	}

	/**
	 * @dataProvider customResponseProvider
	 */
	public function testCustomPastEndResponse( $body, $headers, $status ) {
		$mock = $this->getMockBuilder('\donatj\MockWebServer\RequestInfo')->disableOriginalConstructor()->getMock();

		$x = new ResponseStack;
		$x->setPastEndResponse(new \donatj\MockWebServer\Response($body, $headers, $status));

		$this->assertSame($body, $x->getBody($mock));
		$this->assertSame($status, $x->getStatus($mock));
		$this->assertSame($headers, $x->getHeaders($mock));
		$this->assertSame(false, $x->next());
	}

	public function customResponseProvider() {
		return [
			[ 'PastEnd', [ 'HeaderA' => 'BVAL' ], 420 ],
			[ ' Leading and trailing whitespace ', [], 0 ],
		];
	}

}
