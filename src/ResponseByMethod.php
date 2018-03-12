<?php

namespace donatj\MockWebServer;

class ResponseByMethod implements ResponseInterface {

	const METHOD_GET     = 'GET';
	const METHOD_POST    = 'POST';
	const METHOD_PUT     = 'PUT';
	const METHOD_PATCH   = 'PATCH';
	const METHOD_DELETE  = 'DELETE';
	const METHOD_HEAD    = 'HEAD';
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_TRACE   = 'TRACE';

	/** @var ResponseInterface[] */
	private $responses = [];

	/** @var ResponseInterface */
	private $default;

	/**
	 * MethodResponse constructor.
	 *
	 * @param ResponseInterface[]    $responses An array of responses keyed by their method.
	 * @param ResponseInterface|null $defaultResponse The fallthrough response to return if a response for a given
	 * method is not found. If this is not defined the server will return an HTTP 501 error.
	 */
	public function __construct( array $responses = [], ResponseInterface $defaultResponse = null ) {
		foreach( $responses as $method => $response ) {
			$this->setMethodResponse($method, $response);
		}

		if( $defaultResponse instanceof ResponseInterface ) {
			$this->default = $defaultResponse;
		}else {
			$this->default = new Response('MethodResponse - Method Not Defined', [], 501);
		}
	}

	public function getRef() {
		$refBase = $this->default->getRef();
		foreach( $this->responses as $response ) {
			$refBase .= $response->getRef();
		}

		return md5($refBase);
	}

	public function getBody( RequestInfo $request ) {
		return $this->getMethodResponse($request)->getBody($request);
	}

	public function getHeaders( RequestInfo $request ) {
		return $this->getMethodResponse($request)->getHeaders($request);
	}

	public function getStatus( RequestInfo $request ) {
		return $this->getMethodResponse($request)->getStatus($request);
	}

	/**
	 * @param RequestInfo $request
	 * @return ResponseInterface
	 */
	private function getMethodResponse( RequestInfo $request ) {
		$method = $request->getRequestMethod();
		if( isset($this->responses[$method]) ) {
			return $this->responses[$method];
		}

		return $this->default;
	}

	/**
	 * Set the Response for the Given Method
	 *
	 * @param string            $method
	 * @param ResponseInterface $response
	 */
	public function setMethodResponse( $method, ResponseInterface $response ) {
		$this->responses[$method] = $response;
	}

}
