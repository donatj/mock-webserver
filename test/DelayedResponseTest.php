<?php

namespace Test;

use donatj\MockWebServer\DelayedResponse;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\Responses\DefaultResponse;
use donatj\MockWebServer\ResponseStack;
use PHPUnit\Framework\TestCase;

class DelayedResponseTest extends TestCase {

	public function testInitialize() : void {
		$foundDelay = null;
		$resp       = new DelayedResponse(new DefaultResponse, 1234, function ( int $delay ) use ( &$foundDelay ) {
			$foundDelay = $delay;
		});

		$requestInfo = $this->getMockBuilder(RequestInfo::class)
			->disableOriginalConstructor()
			->getMock();

		$resp->initialize($requestInfo);

		$this->assertSame(1234, $foundDelay);
	}

	public function testNext() : void {
		$resp = new DelayedResponse(new DefaultResponse, 1234);
		$this->assertFalse($resp->next());

		$resp = new DelayedResponse(new DelayedResponse(new DefaultResponse, 1234), 1234);
		$this->assertFalse($resp->next());

		$resp = new DelayedResponse(new ResponseStack(
			new Response('foo'),
			new Response('bar'),
			new Response('baz')
		), 1234);

		$req = $this->getMockBuilder(RequestInfo::class)
			->disableOriginalConstructor()
			->getMock();

		$this->assertSame('foo', $resp->getBody($req));
		$this->assertTrue($resp->next());
		$this->assertSame('bar', $resp->getBody($req));
		$this->assertTrue($resp->next());
		$this->assertSame('baz', $resp->getBody($req));
		$this->assertFalse($resp->next());
	}

	public function testGetRef() : void {
		$resp1 = new DelayedResponse(new DefaultResponse, 1234);
		$this->assertNotFalse(
			preg_match('/^[a-f0-9]{32}$/', $resp1->getRef()),
			'Ref must be a 32 character hex string'
		);

		$resp2 = new DelayedResponse(new Response('foo'), 1234);
		$this->assertNotFalse(
			preg_match('/^[a-f0-9]{32}$/', $resp2->getRef()),
			'Ref is a 32 character hex string'
		);

		$this->assertNotSame($resp1->getRef(), $resp2->getRef(), 'Ref is unique per response');
	}

}
