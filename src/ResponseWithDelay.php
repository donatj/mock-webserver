<?php

namespace donatj\MockWebServer;

class ResponseWithDelay implements InitializingResponse {

	/**
	 * @var int
	 */
	protected $delay;
	/**
	 * @var \donatj\MockWebServer\ResponseInterface
	 */
	private $response;

	/**
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @param int                                     $delay Microseconds to delay the response
	 */
	public function __construct(
		ResponseInterface $response,
		$delay
	) {
		$this->response = $response;
		$this->delay    = $delay;
	}

	/**
	 * @inheritDoc
	 */
	public function getRef() {
		return $this->response->getRef();
	}

	/**
	 * @inheritDoc
	 */
	public function initialize( RequestInfo $request ) {
		usleep($this->delay);
	}

	/**
	 * @inheritDoc
	 */
	public function getBody( RequestInfo $request ) {
		return $this->response->getBody($request);
	}

	/**
	 * @inheritDoc
	 */
	public function getHeaders( RequestInfo $request ) {
		return $this->response->getHeaders($request);
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus( RequestInfo $request ) {
		return $this->response->getStatus($request);
	}

}
