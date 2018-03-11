<?php

namespace donatj\MockWebServer;

class Response implements ResponseInterface {

	/**
	 * @var string
	 */
	private $body;
	/**
	 * @var array
	 */
	private $headers;
	/**
	 * @var int
	 */
	private $status;

	/**
	 * Response constructor.
	 *
	 * @param string $body
	 * @param array  $headers
	 * @param int    $status
	 */
	public function __construct( $body, array $headers = [], $status = 200 ) {
		$this->body    = $body;
		$this->headers = $headers;
		$this->status  = $status;
	}

    /**
     * @inheritdoc
     */
	public static function create($data) {
	    if (is_object($data)) {
	        $data = (array) $data;
        }

        if (!isset($data['headers'])) {
            $data['headers'] = [];
        }

        if (is_object($data['headers'])) {
	        $data['headers'] = (array) $data['headers'];
        }

        if (!isset($data['status'])) {
            $data['status'] = 200;
        }

        if (!isset($data['body'])) {
            $data['body'] = '';
        }

        if (!is_string($data['body'])) {
            $data['body'] = json_encode($data['body']);
        }

        return new self($data['body'], $data['headers'], (int) $data['status']);
    }

	/**
	 * @inheritdoc
	 */
	public function getRef() {
		$content = json_encode([
			self::RESPONSE_BODY    => $this->body,
			self::RESPONSE_STATUS  => $this->status,
			self::RESPONSE_HEADERS => $this->headers,
		]);

		return md5($content);
	}

	/**
	 * @inheritdoc
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * @inheritdoc
	 */
	public function getStatus() {
		return $this->status;
	}
}
