<?php

namespace donatj\MockWebServer;

/**
 * Class InternalServer
 *
 * @internal
 */
class InternalServer {

	private $tmpPath;
	private $request;
	private $parsedUri;
	private $server;
	/**
	 * @var callable
	 */
	private $header;


	/**
	 * InternalServer constructor.
	 *
	 * @param string        $tmpPath
	 * @param array         $server
	 * @param array         $get
	 * @param array         $post
	 * @param array         $files
	 * @param array         $cookie
	 * @param array         $HEADERS
	 * @param string        $INPUT
	 * @param callable|null $header
	 */
	function __construct( $tmpPath, array $server, array $get, array $post, array $files, array $cookie, array $HEADERS, $INPUT, callable $header = null ) {
		if( is_null($header) ) {
			$header = "\\header";
		}

		$this->tmpPath = $tmpPath;

		parse_str($INPUT, $PARSED_INPUT);
		$this->parsedUri = parse_url($server['REQUEST_URI']);

		$this->request = [
			'_GET'               => $get,
			'_POST'              => $post,
			'_FILES'             => $files,
			'_COOKIE'            => $cookie,
			'HEADERS'            => $HEADERS,
			'METHOD'             => $server['REQUEST_METHOD'],
			'INPUT'              => $INPUT,
			'PARSED_INPUT'       => $PARSED_INPUT,
			'REQUEST_URI'        => $server['REQUEST_URI'],
			'PARSED_REQUEST_URI' => $this->parsedUri,
		];

		$this->logRequest($this->request);
		$this->server = $server;
		$this->header = $header;
	}

	private function logRequest( array $request ) {
		$reqStr = json_encode($request);
		file_put_contents($this->tmpPath . DIRECTORY_SEPARATOR . MockWebServer::LAST_REQUEST_FILE, $reqStr);
		file_put_contents($this->tmpPath . DIRECTORY_SEPARATOR . 'request.' . microtime(true), $reqStr);
	}

	public static function aliasPath( $tmpPath, $path ) {
		$path = '/' . ltrim($path, '/');

		return $tmpPath . DIRECTORY_SEPARATOR . 'alias.' . md5($path);
	}

	public function __invoke() {
		$path      = false;
		$aliasPath = self::aliasPath($this->tmpPath, $this->parsedUri['path']);
		if( file_exists($aliasPath) ) {
			if( $path = file_get_contents($aliasPath) ) {
				$path = $this->tmpPath . DIRECTORY_SEPARATOR . $path;
			}
		} elseif( preg_match('%^/' . preg_quote(MockWebServer::VND) . '/([0-9a-fA-F]{32})$%', $this->server['REQUEST_URI'], $matches) ) {
			$path = $this->tmpPath . DIRECTORY_SEPARATOR . $matches[1];
		}

		if( $path !== false ) {
			if( is_readable($path) ) {
				$content  = file_get_contents($path);
				$response = json_decode($content, true);

				http_response_code($response[MockWebServer::RESPONSE_STATUS]);

				foreach( $response[MockWebServer::RESPONSE_HEADERS] as $key => $header ) {
					if( is_int($key) ) {
						header($header);
					} else {
						header("{$key}: {$header}");
					}
				}

				if( $response[MockWebServer::RESPONSE_BODY] !== false ) {
					echo $response[MockWebServer::RESPONSE_BODY];

					return;
				}
			} else {
				http_response_code(404);
				echo MockWebServer::VND . ": Resource '{$path}' not found!\n";

				return;
			}
		} else {
			header('Content-Type: application/json');
		}

		echo json_encode($this->request, JSON_PRETTY_PRINT);
	}


}
