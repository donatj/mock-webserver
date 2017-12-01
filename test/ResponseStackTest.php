<?php

use donatj\MockWebServer\ResponseStack;

class ResponseStackTest extends \PHPUnit_Framework_TestCase {

	public function testEmpty() {
		$x = new ResponseStack();

		$this->assertSame('Past the end of the ResponseStack', $x->getBody());
		$this->assertSame(404, $x->getStatus());
		$this->assertSame([], $x->getHeaders());
		$this->assertSame(false, $x->next());
	}

	/**
	 * @dataProvider customResponseProvider
	 */
	public function testCustomPastEndResponse( $body, $headers, $status ) {
		$x = new ResponseStack();
		$x->setPastEndResponse(new \donatj\MockWebServer\Response($body, $headers, $status));

		$this->assertSame($body, $x->getBody());
		$this->assertSame($status, $x->getStatus());
		$this->assertSame($headers, $x->getHeaders());
		$this->assertSame(false, $x->next());
	}

	public function customResponseProvider() {
		return [
			[ 'PastEnd', [ 'HeaderA' => 'BVAL' ], 420 ],
			[ ' Leading and trailing whitespace ', [], 0 ],
		];
	}

}
