<?php

namespace donatj\MockWebServer;

class RequestInfo implements \JsonSerializable {

	const JSON_KEY_GET          = '_GET';
	const JSON_KEY_POST         = '_POST';
	const JSON_KEY_FILES        = '_FILES';
	const JSON_KEY_COOKIE       = '_COOKIE';
	const JSON_KEY_HEADERS      = 'HEADERS';
	const JSON_KEY_METHOD       = 'METHOD';
	const JSON_KEY_INPUT        = 'INPUT';
	const JSON_KEY_PARSED_INPUT = 'PARSED_INPUT';
	const JSON_KEY_REQUEST_URI  = 'REQUEST_URI';

	const JSON_KEY_PARSED_REQUEST_URI = 'PARSED_REQUEST_URI';

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
	 * @var array|null
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
			self::JSON_KEY_GET          => $this->get,
			self::JSON_KEY_POST         => $this->post,
			self::JSON_KEY_FILES        => $this->files,
			self::JSON_KEY_COOKIE       => $this->cookie,
			self::JSON_KEY_HEADERS      => $this->HEADERS,
			self::JSON_KEY_METHOD       => $this->getRequestMethod(),
			self::JSON_KEY_INPUT        => $this->INPUT,
			self::JSON_KEY_PARSED_INPUT => $this->PARSED_INPUT,
			self::JSON_KEY_REQUEST_URI  => $this->getRequestUri(),

			self::JSON_KEY_PARSED_REQUEST_URI => $this->parsedUri,
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

	/**
	 * @return array
	 */
	public function getServer() {
		return $this->server;
	}

	/**
	 * @return array
	 */
	public function getGet() {
		return $this->get;
	}

	/**
	 * @return array
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * @return array
	 */
	public function getFiles() {
		return $this->files;
	}

	/**
	 * @return array
	 */
	public function getCookie() {
		return $this->cookie;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->HEADERS;
	}

	/**
	 * @return string
	 */
	public function getInput() {
		return $this->INPUT;
	}

	/**
	 * @return array|null
	 */
	public function getParsedInput() {
		return $this->PARSED_INPUT;
	}

}
