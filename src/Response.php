<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\RuntimeException;

class Response implements ResponseInterface {

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
		$this->body    = $body;
		$this->headers = $headers;
		$this->status  = $status;
	}

	public function getRef() : string {
		$content = json_encode([
			md5($this->body),
			$this->status,
			$this->headers,
		]);

		if( $content === false ) {
			throw new RuntimeException('Failed to encode response content to JSON: ' . json_last_error_msg());
		}

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
