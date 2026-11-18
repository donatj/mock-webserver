<?php

namespace donatj\MockWebServer;

class Response implements ResponseInterface {

	use ResponseRefTrait;

	/** @var string */
	protected $body;
	/** @var array */
	protected $headers;
	/** @var int */
	protected $status;

	/**
	 * Response constructor.
	 */
	public function __construct( string $body, array $headers = [], int $status = 200 ) {
		$this->initializeResponseRef();
		$this->body    = $body;
		$this->headers = $headers;
		$this->status  = $status;
	}

	public function getBody( RequestInfo $request ) : string {
		return $this->body;
	}

	public function getHeaders( RequestInfo $request ) : array {
		return $this->headers;
	}

	public function getStatus( RequestInfo $request ) : int {
		return $this->status;
	}

}
