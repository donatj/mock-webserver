<?php

namespace donatj\MockWebServer;

interface ResponseInterface {

	/**
	 * Get a unique identifier for the response.
	 *
	 * Expected to be 32 characters of hexadecimal
	 *
	 * @internal
	 */
	public function getRef() : string;

	/**
	 * Get the body of the response
	 *
	 * @internal
	 */
	public function getBody( RequestInfo $request ) : string;

	/**
	 * Get the headers as either an array of key => value or ["Full: Header","OtherFull: Header"]
	 *
	 * @internal
	 */
	public function getHeaders( RequestInfo $request ) : array;

	/**
	 * Get the HTTP Status Code
	 *
	 * @internal
	 */
	public function getStatus( RequestInfo $request ) : int;

}
