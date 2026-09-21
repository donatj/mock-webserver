<?php

namespace donatj\MockWebServer\Responses;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\ResponseInterface;
use donatj\MockWebServer\ResponseRefTrait;

/**
 * Basic Built-In 404 Response
 */
class NotFoundResponse implements ResponseInterface {

	use ResponseRefTrait;

	public function __construct() {
		$this->initializeResponseRef();
	}

	public function getBody( RequestInfo $request ) : string {
		$path = $request->getParsedUri()['path'];

		return MockWebServer::VND . ": Resource '{$path}' not found!\n";
	}

	public function getHeaders( RequestInfo $request ) : array {
		return [];
	}

	public function getStatus( RequestInfo $request ) : int {
		return 404;
	}

}
