<?php

namespace donatj\MockWebServer\Responses;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\ResponseInterface;

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
