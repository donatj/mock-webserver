<?php

namespace donatj\MockWebServer\Responses;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\ResponseInterface;

/**
 * The Built-In Default Response.
 * Results in an HTTP 200 with a JSON encoded version of the incoming Request
 */
class DefaultResponse implements ResponseInterface {

	public function getRef() {
		return md5(MockWebServer::VND . '.default-ref');
	}

	public function getBody( RequestInfo $request ) {
		return json_encode($request, JSON_PRETTY_PRINT) . "\n";
	}

	public function getHeaders( RequestInfo $request ) {
		return [ 'Content-Type' => 'application/json' ];
	}

	public function getStatus( RequestInfo $request ) {
		return 200;
	}
}
