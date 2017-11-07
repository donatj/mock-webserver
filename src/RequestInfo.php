<?php

namespace donatj\MockWebServer;

class RequestInfo implements \JsonSerializable {

	/**
	 * @var mixed
	 */
	private $parsedUri;
	/**
	 * @var array
	 */
	private $server;
	/**
	 * @var array
	 */
	private $get;
	/**
	 * @var array
	 */
	private $post;
	/**
	 * @var array
	 */
	private $files;
	/**
	 * @var array
	 */
	private $cookie;
	/**
	 * @var array
	 */
	private $HEADERS;
	/**
	 * @var string
	 */
	private $INPUT;
	/**
	 * @var array
	 */
	private $PARSED_INPUT;

	/**
	 * @param array  $server
	 * @param array  $get
	 * @param array  $post
	 * @param array  $files
	 * @param array  $cookie
	 * @param array  $HEADERS
	 * @param string $INPUT
	 */
	public function __construct( array $server, array $get, array $post, array $files, array $cookie, array $HEADERS, $INPUT ) {
		$this->server  = $server;
		$this->get     = $get;
		$this->post    = $post;
		$this->files   = $files;
		$this->cookie  = $cookie;
		$this->HEADERS = $HEADERS;
		$this->INPUT   = $INPUT;

		parse_str($INPUT, $PARSED_INPUT);
		$this->PARSED_INPUT = $PARSED_INPUT;

		$this->parsedUri = parse_url($server['REQUEST_URI']);
	}


	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return [
			'_GET'         => $this->get,
			'_POST'        => $this->post,
			'_FILES'       => $this->files,
			'_COOKIE'      => $this->cookie,
			'HEADERS'      => $this->HEADERS,
			'METHOD'       => $this->getRequestMethod(),
			'INPUT'        => $this->INPUT,
			'PARSED_INPUT' => $this->PARSED_INPUT,
			'REQUEST_URI'  => $this->getRequestUri(),

			'PARSED_REQUEST_URI' => $this->parsedUri,
		];
	}

	/**
	 * @return mixed
	 */
	public function getParsedUri() {
		return $this->parsedUri;
	}

	/**
	 * @return string
	 */
	public function getRequestUri() {
		return $this->server['REQUEST_URI'];
	}

	/**
	 * @return string
	 */
	public function getRequestMethod() {
		return $this->server['REQUEST_METHOD'];
	}
}
