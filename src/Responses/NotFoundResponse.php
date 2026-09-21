<?php

namespace donatj\MockWebServer\Responses;

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\ResponseInterface;

/**
 * Basic Built-In 404 Response
 */
class NotFoundResponse implements ResponseInterface {

	/** @var string */
	private $ref;

	public function __construct() {
		$this->ref = bin2hex(random_bytes(16));
	}

	public function getRef() : string {
		return $this->ref;
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
