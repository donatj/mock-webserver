<?php

namespace donatj\MockWebServer;

interface ResponseInterface {

	const RESPONSE_BODY    = 'body';
	const RESPONSE_STATUS  = 'status';
	const RESPONSE_HEADERS = 'headers';

	/**
	 * Get a unique identifier for the response.
	 *
	 * Expected to be 32 characters of hexadecimal
	 *
	 * @internal
	 * @return string
	 */
	public function getRef();

	/**
	 * Get the body of the response
	 *
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @return string
	 */
	public function getBody(RequestInfo $request);

	/**
	 * Get the headers as either an array of key => value or ["Full: Header","OtherFull: Header"]
	 *
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @return array
	 */
	public function getHeaders(RequestInfo $request);

	/**
	 * Get the HTTP Status Code
	 *
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @return int
	 */
	public function getStatus(RequestInfo $request);

}
