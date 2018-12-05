<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\ServerException;
use donatj\MockWebServer\Responses\DefaultResponse;
use donatj\MockWebServer\Responses\NotFoundResponse;

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

	const DEFAULT_REF = 'default';

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
	 * @param int|null $int
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

	/**
	 * @param string $ref
	 * @return ResponseInterface|null
	 */
	private function responseForRef( $ref ) {
		$path = $this->tmpPath . DIRECTORY_SEPARATOR . $ref;
		if( !is_readable($path) ) {
			return null;
		}

		$content  = file_get_contents($path);
		$response = unserialize($content);
		if( !$response instanceof ResponseInterface ) {
			throw new ServerException('invalid serialized response');
		}

		return $response;
	}

	public function __invoke() {
		$ref = $this->getRefForUri($this->request->getParsedUri()['path']);

		if( $ref !== null ) {
			$response = $this->responseForRef($ref);
			if( $response ) {
				$this->sendResponse($response);

				return;
			}

			$this->sendResponse(new NotFoundResponse);

			return;
		}

		$response = $this->responseForRef(self::DEFAULT_REF);
		if( $response ) {
			$this->sendResponse($response);

			return;
		}

		$this->sendResponse(new DefaultResponse);
	}

	protected function sendResponse( ResponseInterface $response ) {
		if( $response instanceof InitializingResponseInterface ) {
			$response->initialize($this->request);
		}

		http_response_code($response->getStatus($this->request));

		foreach( $response->getHeaders($this->request) as $key => $header ) {
			if( is_int($key) ) {
				call_user_func($this->header, $header);
			} else {
				call_user_func($this->header, "{$key}: {$header}");
			}
		}

		echo $response->getBody($this->request);

		if( $response instanceof MultiResponseInterface ) {
			$response->next();
			self::storeResponse($this->tmpPath, $response);
		}
	}

	/**
	 * @return string|null
	 */
	protected function getRefForUri( $uriPath ) {
		$aliasPath = self::aliasPath($this->tmpPath, $uriPath);

		if( file_exists($aliasPath) ) {
			if( $path = file_get_contents($aliasPath) ) {
				return $path;
			}
		} elseif( preg_match('%^/' . preg_quote(MockWebServer::VND, '%') . '/([0-9a-fA-F]{32})$%', $uriPath, $matches) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * @param string                                  $tmpPath
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @return string
	 * @internal
	 */
	public static function storeResponse( $tmpPath, ResponseInterface $response ) {
		$ref = $response->getRef();
		self::storeRef($response, $tmpPath, $ref);

		return $ref;
	}

	/**
	 * @param string                                  $tmpPath
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @return void
	 * @internal
	 */
	public static function storeDefaultResponse( $tmpPath, ResponseInterface $response ) {
		self::storeRef($response, $tmpPath, self::DEFAULT_REF);
	}

	/**
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @param string                                  $tmpPath
	 * @param string                                  $ref
	 */
	private static function storeRef( ResponseInterface $response, $tmpPath, $ref ) {
		$content = serialize($response);

		if( !file_put_contents($tmpPath . DIRECTORY_SEPARATOR . $ref, $content) ) {
			throw new Exceptions\RuntimeException('Failed to write temporary content');
		}
	}

}
