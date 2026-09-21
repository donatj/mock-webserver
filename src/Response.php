<?php

namespace donatj\MockWebServer;

class Response implements ResponseInterface {

	/** @var string */
	private $ref;
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
		$this->ref     = bin2hex(random_bytes(16));
		$this->body    = $body;
		$this->headers = $headers;
		$this->status  = $status;
	}

	public function getRef() : string {
		return $this->ref;
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
