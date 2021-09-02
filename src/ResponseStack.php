<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\RuntimeException;

/**
 * ResponseStack is used to store multiple responses for a request issued by the server in order.
 *
 * When the stack is empty, the server will return a customizable response defaulting to a 404.
 */
class ResponseStack implements MultiResponseInterface {

	private $ref;

	/** @var \donatj\MockWebServer\ResponseInterface[] */
	private $responses = [];

	/** @var \donatj\MockWebServer\ResponseInterface|null */
	protected $currentResponse;

	/** @var \donatj\MockWebServer\ResponseInterface */
	protected $pastEndResponse;

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
		$this->pastEndResponse = new Response('Past the end of the ResponseStack', [], 404);
	}

	public function next() : bool {
		array_shift($this->responses);
		$this->currentResponse = reset($this->responses) ?: null;

		return (bool)$this->currentResponse;
	}

	public function getRef() : string {
		return $this->ref;
	}

	public function getBody( RequestInfo $request ) : string {
		return $this->currentResponse ?
			$this->currentResponse->getBody($request) :
			$this->pastEndResponse->getBody($request);
	}

	public function getHeaders( RequestInfo $request ) : array {
		return $this->currentResponse ?
			$this->currentResponse->getHeaders($request) :
			$this->pastEndResponse->getHeaders($request);
	}

	public function getStatus( RequestInfo $request ) : int {
		return $this->currentResponse ?
			$this->currentResponse->getStatus($request) :
			$this->pastEndResponse->getStatus($request);
	}

	/**
	 * Gets the response returned when the stack is exhausted.
	 */
	public function getPastEndResponse() : ResponseInterface {
		return $this->pastEndResponse;
	}

	/**
	 * Set the response to return when the stack is exhausted.
	 */
	public function setPastEndResponse( ResponseInterface $pastEndResponse ) {
		$this->pastEndResponse = $pastEndResponse;
	}

}
