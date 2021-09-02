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

	/** @var string */
	private $tmpPath;
	/** @var \donatj\MockWebServer\RequestInfo */
	private $request;
	/** @var callable */
	private $header;

	private const DEFAULT_REF = 'default';

	public function __construct( string $tmpPath, RequestInfo $request, ?callable $header = null ) {
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
	 * @internal
	 */
	public static function incrementRequestCounter( string $tmpPath, ?int $int = null ) : int {
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

	private function logRequest( RequestInfo $request, int $count ) : void {
		$reqStr = serialize($request);
		file_put_contents($this->tmpPath . DIRECTORY_SEPARATOR . MockWebServer::LAST_REQUEST_FILE, $reqStr);
		file_put_contents($this->tmpPath . DIRECTORY_SEPARATOR . 'request.' . $count, $reqStr);
	}

	/**
	 * @internal
	 */
	public static function aliasPath( string $tmpPath, string $path ) : string {
		$path = '/' . ltrim($path, '/');

		return sprintf('%s%salias.%s',
			$tmpPath,
			DIRECTORY_SEPARATOR,
			md5($path)
		);
	}

	private function responseForRef( string $ref ) : ?ResponseInterface {
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

	public function __invoke() : void {
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

	protected function sendResponse( ResponseInterface $response ) : void {
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

	protected function getRefForUri( $uriPath ) : ?string {
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
	 * @internal
	 */
	public static function storeResponse( string $tmpPath, ResponseInterface $response ) : string {
		$ref = $response->getRef();
		self::storeRef($response, $tmpPath, $ref);

		return $ref;
	}

	/**
	 * @internal
	 */
	public static function storeDefaultResponse( string $tmpPath, ResponseInterface $response ) : void {
		self::storeRef($response, $tmpPath, self::DEFAULT_REF);
	}

	private static function storeRef( ResponseInterface $response, string $tmpPath, string $ref ) {
		$content = serialize($response);

		if( !file_put_contents($tmpPath . DIRECTORY_SEPARATOR . $ref, $content) ) {
			throw new Exceptions\RuntimeException('Failed to write temporary content');
		}
	}

}
