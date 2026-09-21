<?php

namespace donatj\MockWebServer;

/**
 * DelayedResponse wraps a response, causing it when called to be delayed by a specified number of microseconds.
 *
 * This is useful for simulating slow responses and testing timeouts.
 */
class DelayedResponse implements InitializingResponseInterface, MultiResponseInterface {

	/** @var string */
	private $ref;
	/** @var int Microseconds to delay the response by. */
	protected $delay;
	/** @var \donatj\MockWebServer\ResponseInterface */
	protected $response;
	/** @var callable */
	protected $usleep;

	/**
	 * @param int $delay Microseconds to delay the response
	 */
	public function __construct(
		ResponseInterface $response,
		int $delay,
		?callable $usleep = null
	) {
		$this->ref      = bin2hex(random_bytes(16));
		$this->response = $response;
		$this->delay    = $delay;

		$this->usleep = '\\usleep';
		if( $usleep ) {
			$this->usleep = $usleep;
		}
	}

	public function getRef() : string {
		return $this->ref;
	}

	public function initialize( RequestInfo $request ) : void {
		($this->usleep)($this->delay);
	}

	public function getBody( RequestInfo $request ) : string {
		return $this->response->getBody($request);
	}

	public function getHeaders( RequestInfo $request ) : array {
		return $this->response->getHeaders($request);
	}

	public function getStatus( RequestInfo $request ) : int {
		return $this->response->getStatus($request);
	}

	public function next() : bool {
		if( $this->response instanceof MultiResponseInterface ) {
			return $this->response->next();
		}

		return false;
	}

}
