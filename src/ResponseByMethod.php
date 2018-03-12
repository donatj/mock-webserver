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
	 * @param ResponseInterface[] $responses An array of responses keyed by their method.
	 */
	public function __construct( array $responses = [] ) {
		foreach( $responses as $method => $response ) {
			$this->setMethodResponse($method, $response);
		}

		$this->default = new Response('MethodResponse - Method Not Defined', [], 501);
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
