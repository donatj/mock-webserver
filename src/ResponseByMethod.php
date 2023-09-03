<?php

namespace donatj\MockWebServer;

/**
 * ResponseByMethod is used to vary the response to a request by the called HTTP Method.
 */
class ResponseByMethod implements ResponseInterface {

	public const METHOD_GET     = 'GET';
	public const METHOD_POST    = 'POST';
	public const METHOD_PUT     = 'PUT';
	public const METHOD_PATCH   = 'PATCH';
	public const METHOD_DELETE  = 'DELETE';
	public const METHOD_HEAD    = 'HEAD';
	public const METHOD_OPTIONS = 'OPTIONS';
	public const METHOD_TRACE   = 'TRACE';

	/** @var ResponseInterface[] */
	private $responses = [];

	/** @var ResponseInterface */
	private $defaultResponse;

	/**
	 * MethodResponse constructor.
	 *
	 * @param array<string, ResponseInterface> $responses       A map of responses keyed by their method.
	 * @param ResponseInterface|null           $defaultResponse The fallthrough response to return if a response for a given
	 *                                                          method is not found. If this is not defined the server will
	 *                                                          return an HTTP 501 error.
	 */
	public function __construct( array $responses = [], ?ResponseInterface $defaultResponse = null ) {
		foreach( $responses as $method => $response ) {
			$this->setMethodResponse($method, $response);
		}

		if( $defaultResponse ) {
			$this->defaultResponse = $defaultResponse;
		} else {
			$this->defaultResponse = new Response('MethodResponse - Method Not Defined', [], 501);
		}
	}

	public function getRef() : string {
		$refBase = $this->defaultResponse->getRef();
		foreach( $this->responses as $response ) {
			$refBase .= $response->getRef();
		}

		return md5($refBase);
	}

	public function getBody( RequestInfo $request ) : string {
		return $this->getMethodResponse($request)->getBody($request);
	}

	public function getHeaders( RequestInfo $request ) : array {
		return $this->getMethodResponse($request)->getHeaders($request);
	}

	public function getStatus( RequestInfo $request ) : int {
		return $this->getMethodResponse($request)->getStatus($request);
	}

	private function getMethodResponse( RequestInfo $request ) : ResponseInterface {
		$method = $request->getRequestMethod();

		return $this->responses[$method] ?? $this->defaultResponse;
	}

	/**
	 * Set the Response for the Given Method
	 */
	public function setMethodResponse( string $method, ResponseInterface $response ) : void {
		$this->responses[$method] = $response;
	}

}
