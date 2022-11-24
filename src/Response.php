<?php

namespace donatj\MockWebServer;

class Response implements ResponseInterface {

	/** @var string */
	private $body;
	/** @var array */
	private $headers;
	/** @var int */
	private $status;

	/**
	 * Response constructor.
	 */
	public function __construct( string $body, array $headers = [], int $status = 200 ) {
		$this->body    = $body;
		$this->headers = $headers;
		$this->status  = $status;
	}

	public function getRef() : string {
		$content = json_encode([
			$this->body,
			$this->status,
			$this->headers,
		]);

		return md5($content);
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
