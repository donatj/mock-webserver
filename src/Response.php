<?php

namespace donatj\MockWebServer;

class Response implements ResponseInterface {

	/**
	 * @var string
	 */
	private $body;
	/**
	 * @var array
	 */
	private $headers;
	/**
	 * @var int
	 */
	private $status;

	/**
	 * Response constructor.
	 *
	 * @param string $body
	 * @param array  $headers
	 * @param int    $status
	 */
	public function __construct( $body, array $headers = [], $status = 200 ) {
		$this->body    = $body;
		$this->headers = $headers;
		$this->status  = $status;
	}

	/**
	 * @inheritdoc
	 */
	public function getRef() {
		$content = json_encode([
			$this->body,
			$this->status,
			$this->headers,
		]);

		return md5($content);
	}

	/**
	 * @inheritdoc
	 */
	public function getBody( RequestInfo $request ) {
		return $this->body;
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaders( RequestInfo $request ) {
		return $this->headers;
	}

	/**
	 * @inheritdoc
	 */
	public function getStatus( RequestInfo $request ) {
		return $this->status;
	}
}
