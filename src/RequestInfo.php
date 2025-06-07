<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\RuntimeException;

class RequestInfo implements \JsonSerializable {

	public const JSON_KEY_GET          = '_GET';
	public const JSON_KEY_POST         = '_POST';
	public const JSON_KEY_FILES        = '_FILES';
	public const JSON_KEY_COOKIE       = '_COOKIE';
	public const JSON_KEY_HEADERS      = 'HEADERS';
	public const JSON_KEY_METHOD       = 'METHOD';
	public const JSON_KEY_INPUT        = 'INPUT';
	public const JSON_KEY_PARSED_INPUT = 'PARSED_INPUT';
	public const JSON_KEY_REQUEST_URI  = 'REQUEST_URI';

	public const JSON_KEY_PARSED_REQUEST_URI = 'PARSED_REQUEST_URI';

	/** @var array */
	private $parsedUri;
	/** @var array */
	private $server;
	/** @var array */
	private $get;
	/** @var array */
	private $post;
	/** @var array */
	private $files;
	/** @var array */
	private $cookie;
	/** @var array */
	private $HEADERS;
	/** @var string */
	private $INPUT;
	/** @var array|null */
	private $PARSED_INPUT;

	public function __construct(
		array $server,
		array $get,
		array $post,
		array $files,
		array $cookie,
		array $HEADERS,
		string $INPUT
	) {
		$this->server  = $server;
		$this->get     = $get;
		$this->post    = $post;
		$this->files   = $files;
		$this->cookie  = $cookie;
		$this->HEADERS = $HEADERS;
		$this->INPUT   = $INPUT;

		parse_str($INPUT, $PARSED_INPUT);
		$this->PARSED_INPUT = $PARSED_INPUT;

		if( !isset($server['REQUEST_URI']) ) {
			throw new RuntimeException('REQUEST_URI not set');
		}

		if( !isset($server['REQUEST_METHOD']) ) {
			throw new RuntimeException('REQUEST_METHOD not set');
		}

		$parsedUrl = parse_url($server['REQUEST_URI']);
		if( $parsedUrl === false ) {
			throw new RuntimeException('Failed to parse REQUEST_URI: ' . $server['REQUEST_URI']);
		}

		$this->parsedUri = $parsedUrl;
	}

	/**
	 * Specify data which should be serialized to JSON
	 */
	public function jsonSerialize() : array {
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
	 * @return array
	 */
	public function getParsedUri() {
		return $this->parsedUri;
	}

	public function getRequestUri() : string {
		return $this->server['REQUEST_URI'];
	}

	public function getRequestMethod() : string {
		return $this->server['REQUEST_METHOD'];
	}

	public function getServer() : array {
		return $this->server;
	}

	public function getGet() : array {
		return $this->get;
	}

	public function getPost() : array {
		return $this->post;
	}

	public function getFiles() : array {
		return $this->files;
	}

	public function getCookie() : array {
		return $this->cookie;
	}

	public function getHeaders() : array {
		return $this->HEADERS;
	}

	public function getInput() : string {
		return $this->INPUT;
	}

	public function getParsedInput() : ?array {
		return $this->PARSED_INPUT;
	}

}
