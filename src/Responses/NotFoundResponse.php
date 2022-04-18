<?php

namespace donatj\MockWebServer\Responses;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\ResponseInterface;

/**
 * Basic Built-In 404 Response
 */
class NotFoundResponse implements ResponseInterface {

	public function getRef() {
		return md5(MockWebServer::VND . '.not-found');
	}

	public function getBody( RequestInfo $request ) {
		$path = $request->getParsedUri()['path'];

		return MockWebServer::VND . ": Resource '{$path}' not found!\n";
	}

	public function getHeaders( RequestInfo $request ) {
		return [];
	}

	public function getStatus( RequestInfo $request ) {
		return 404;
	}
}
