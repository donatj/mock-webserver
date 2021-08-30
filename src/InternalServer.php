<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\ServerException;

/**
 * Class InternalServer
 *
 * @internal
 */
class InternalServer {

	/**
	 * @var string
	 */
	private $tmpPath;
	/**
	 * @var \donatj\MockWebServer\RequestInfo
	 */
	private $request;
	/**
	 * @var callable
	 */
	private $header;

	/**
	 * InternalServer constructor.
	 *
	 * @param string                            $tmpPath
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @param callable|null                     $header
	 */
	public function __construct( $tmpPath, RequestInfo $request, callable $header = null ) {
		if( $header === null ) {
			$header = "\\header";
		}

		$this->tmpPath = $tmpPath;

		$count = self::incrementRequestCounter($this->tmpPath);
		$this->logRequest($request, $count);

		$this->header  = $header;
		$this->request = $request;
	}

	/**
	 * @param string   $tmpPath
	 * @param null|int $int
	 * @return int
	 */
	public static function incrementRequestCounter( $tmpPath, $int = null ) {
		$countFile = $tmpPath . DIRECTORY_SEPARATOR . MockWebServer::REQUEST_COUNT_FILE;

		if( $int === null ) {
			$newInt = file_get_contents($countFile);
			if( !is_string($newInt) ) {
				throw new ServerException('failed to fetch request count');
			}
			$int = (int)$newInt + 1;
		}

		file_put_contents($countFile, (string)$int);

		return (int)$int;
	}

	private function logRequest( RequestInfo $request, $count ) {
		$reqStr = serialize($request);
		file_put_contents($this->tmpPath . DIRECTORY_SEPARATOR . MockWebServer::LAST_REQUEST_FILE, $reqStr);
		file_put_contents($this->tmpPath . DIRECTORY_SEPARATOR . 'request.' . $count, $reqStr);
	}

	public static function aliasPath( $tmpPath, $path ) {
		$path = '/' . ltrim($path, '/');

		return sprintf('%s%salias.%s',
			$tmpPath,
			DIRECTORY_SEPARATOR,
			md5($path)
		);
	}

	public function __invoke() {
		$path = $this->getDataPath();

		if( $path !== null ) {
			if( is_readable($path) ) {
				$content  = file_get_contents($path);
				$response = unserialize($content);
				if( !$response instanceof ResponseInterface ) {
					throw new ServerException('invalid serialized response');
				}

				$this->sendResponse($response, $this->request);

				if( $response instanceof MultiResponseInterface ) {
					$response->next();
					self::storeResponse($this->tmpPath, $response);
				}

				return;
			}

			http_response_code(404);
			echo MockWebServer::VND . ": Resource '{$path}' not found!\n";

			return;
		}

		header('Content-Type: application/json');

		echo json_encode($this->request, JSON_PRETTY_PRINT);
	}

	protected function sendResponse( ResponseInterface $response, RequestInfo $request ) {
		http_response_code($response->getStatus($request));

		foreach( $response->getHeaders($request) as $key => $header ) {
			if( is_int($key) ) {
				call_user_func($this->header, $header);
			} else {
				call_user_func($this->header, "{$key}: {$header}");
			}
		}

		echo $response->getBody($request);
	}

	/**
	 * @return string|null
	 */
	protected function getDataPath() {
		$uriPath   = $this->request->getParsedUri()['path'];
		$aliasPath = self::aliasPath($this->tmpPath, $uriPath);

		if( file_exists($aliasPath) ) {
			if( $path = file_get_contents($aliasPath) ) {
				return $this->tmpPath . DIRECTORY_SEPARATOR . $path;
			}
		} elseif( preg_match('%^/' . preg_quote(MockWebServer::VND, '%') . '/([0-9a-fA-F]{32})$%', $uriPath, $matches) ) {
			return $this->tmpPath . DIRECTORY_SEPARATOR . $matches[1];
		}

		return null;
	}

	/**
	 * @internal
	 * @param string                                  $tmpPath
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @return string
	 */
	public static function storeResponse( $tmpPath, ResponseInterface $response ) {
		$ref     = $response->getRef();
		$content = serialize($response);

		if( !file_put_contents($tmpPath . DIRECTORY_SEPARATOR . $ref, $content) ) {
			throw new Exceptions\RuntimeException('Failed to write temporary content');
		}

		return $ref;
	}

}
