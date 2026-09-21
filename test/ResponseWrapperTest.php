<?php

namespace Test;

use donatj\MockWebServer\DelayedResponse;
use donatj\MockWebServer\InitializingResponseInterface;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\ResponseByMethod;
use donatj\MockWebServer\ResponseStack;
use PHPUnit\Framework\TestCase;

class ResponseWrapperTest extends TestCase {

	public function testDelayedResponseInitializesWrappedResponse() : void {
		$events   = [];
		$request  = $this->getRequestInfo();
		$response = $this->createMock(InitializingResponseInterface::class);

		$response->expects($this->once())
			->method('initialize')
			->with($request)
			->willReturnCallback(function () use ( &$events ) : void {
				$events[] = 'response';
			});

		$delayed = new DelayedResponse($response, 1234, function ( int $delay ) use ( &$events ) : void {
			$events[] = 'delay:' . $delay;
		});
		$delayed->initialize($request);

		$this->assertSame([ 'delay:1234', 'response' ], $events);
	}

	public function testResponseByMethodInitializesSelectedResponse() : void {
		$request  = $this->getRequestInfo(ResponseByMethod::METHOD_GET);
		$response = $this->createMock(InitializingResponseInterface::class);

		$response->expects($this->once())->method('initialize')->with($request);

		$byMethod = new ResponseByMethod([ ResponseByMethod::METHOD_GET => $response ]);
		$this->assertInstanceOf(InitializingResponseInterface::class, $byMethod);
		$byMethod->initialize($request);
	}

	public function testResponseStackInitializesPastEndResponse() : void {
		$request  = $this->getRequestInfo();
		$response = $this->createMock(InitializingResponseInterface::class);

		$response->expects($this->once())->method('initialize')->with($request);

		$stack = new ResponseStack;
		$stack->setPastEndResponse($response);
		$stack->initialize($request);
	}

	private function getRequestInfo( string $method = 'GET' ) : RequestInfo {
		$request = $this->getMockBuilder(RequestInfo::class)
			->disableOriginalConstructor()
			->getMock();
		$request->method('getRequestMethod')->willReturn($method);

		return $request;
	}

}
