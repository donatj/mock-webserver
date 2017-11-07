<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\RuntimeException;

class ResponseStack implements MultiResponseInterface {

	private $ref;

	/**
	 * @var \donatj\MockWebServer\ResponseInterface[]
	 */
	private $responses = [];

	/**
	 * @var \donatj\MockWebServer\ResponseInterface|null
	 */
	protected $currentResponse;

	/**
	 * ResponseStack constructor.
	 *
	 * Accepts a variable number of RequestInterface objects
	 */
	public function __construct() {
		$responses = func_get_args();
		$refBase   = '';
		foreach( $responses as $response ) {
			if( !$response instanceof ResponseInterface ) {
				throw new RuntimeException('invalid response given - must be an instance of ResponseInterface');
			}

			$this->responses[] = $response;

			$refBase .= $response->getRef();
		}

		$this->ref = md5($refBase);

		$this->currentResponse = reset($this->responses) ?: null;
	}


	/**
	 * @return bool
	 */
	public function next() {
		array_shift($this->responses);
		$this->currentResponse = reset($this->responses) ?: null;

		return (bool)$this->currentResponse;
	}

	/**
	 * @inheritdoc
	 */
	public function getRef() {
		return $this->ref;
	}

	/**
	 * @inheritdoc
	 */
	public function getBody() {
		return $this->currentResponse ? $this->currentResponse->getBody() : 'Past the end of the ResponseStack';
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaders() {
		return $this->currentResponse ? $this->currentResponse->getHeaders() : [];
	}

	/**
	 * @inheritdoc
	 */
	public function getStatus() {
		return $this->currentResponse ? $this->currentResponse->getStatus() : 404;
	}
}
