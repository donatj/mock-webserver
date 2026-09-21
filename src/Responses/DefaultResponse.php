<?php

namespace donatj\MockWebServer\Responses;

use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\ResponseInterface;

/**
 * The Built-In Default Response.
 * Results in an HTTP 200 with a JSON encoded version of the incoming Request
 */
class DefaultResponse implements ResponseInterface {

	/** @var string */
	private $ref;

	public function __construct() {
		$this->ref = bin2hex(random_bytes(16));
	}

	public function getRef() : string {
		return $this->ref;
	}

	public function getBody( RequestInfo $request ) : string {
		return json_encode($request, JSON_PRETTY_PRINT) . "\n";
	}

	public function getHeaders( RequestInfo $request ) : array {
		return [ 'Content-Type' => 'application/json' ];
	}

	public function getStatus( RequestInfo $request ) : int {
		return 200;
	}

}
