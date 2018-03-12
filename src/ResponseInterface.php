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
	 * @internal
	 * @return string
	 */
	public function getRef();

	/**
	 * Get the body of the response
	 *
	 * @return string
	 */
	public function getBody();

	/**
	 * Get the headers as either an array of key => value or ["Full: Header","OtherFull: Header"]
	 *
	 * @return array
	 */
	public function getHeaders();

	/**
	 * Get the HTTP Status Code
	 * @return int
	 */
	public function getStatus();

    /**
     * Instantiate a new ResponseInterface object from the given object or array.
     *
     * @param  array|object $data
     * @return string
     */
	public static function create($data);

}
