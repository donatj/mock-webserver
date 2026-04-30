<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\RuntimeException;

class MockWebServer {

	public const VND = 'VND.DonatStudios.MockWebServer';

	public const LAST_REQUEST_FILE  = 'last.request';
	public const REQUEST_COUNT_FILE = 'count.request';

	public const TMP_ENV = 'MOCK_WEB_SERVER_TMP';

	/** @var string */
	private $host;

	/** @var int */
	private $port;

	/** @var string */
	private $tmpDir;

	/**
	 * Platform-specific process runner
	 *
	 * @var ProcessRunnerInterface
	 */
	private $processRunner;

	/**
	 * TestWebServer constructor.
	 *
	 * @param int    $port Network port to run on
	 * @param string $host Listening hostname
	 */
	public function __construct( int $port = 0, string $host = '127.0.0.1' ) {
		$this->host = $host;
		$this->port = $port;
		if( $this->port === 0 ) {
			$this->port = $this->findOpenPort();
		}

		$this->tmpDir = $this->getTmpDir();
		$this->processRunner = $this->createProcessRunner();
	}

	/**
	 * Start the Web Server on the selected port and host
	 */
	public function start() : void {
		if( $this->isRunning() ) {
			return;
		}

		$script = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'server' . DIRECTORY_SEPARATOR . 'server.php';

		InternalServer::incrementRequestCounter($this->tmpDir, 0);

		$env = [ self::TMP_ENV => $this->tmpDir ];

		$this->processRunner->startProcess(PHP_BINARY, $this->host, $this->port, $script, $env);

		for( $i = 0; $i <= 20; $i++ ) {
			usleep(100000);

			$open = @fsockopen($this->host, $this->port);
			if( is_resource($open) ) {
				fclose($open);
				break;
			}
		}

		if( !$this->isRunning() ) {
			throw new Exceptions\ServerException("Failed to start server. Is something already running on port {$this->port}?");
		}
	}

	/**
	 * Is the Web Server currently running?
	 */
	public function isRunning() : bool {
		return $this->processRunner->isRunning();
	}

	/**
	 * Stop the Web Server
	 */
	public function stop() : void {
		$this->processRunner->stop();
	}

	/**
	 * Get the HTTP root of the webserver
	 *  e.g.: http://127.0.0.1:8123
	 */
	public function getServerRoot() : string {
		return "http://{$this->host}:{$this->port}";
	}

	/**
	 * Get a URL providing the specified response.
	 *
	 * @return string URL where response can be found
	 */
	public function getUrlOfResponse( ResponseInterface $response ) : string {
		$ref = InternalServer::storeResponse($this->tmpDir, $response);

		return $this->getServerRoot() . InternalServer::getPathOfRef($ref);
	}

	/**
	 * Set a specified path to provide a specific response
	 */
	public function setResponseOfPath( string $path, ResponseInterface $response ) : string {
		$ref = InternalServer::storeResponse($this->tmpDir, $response);

		$aliasPath = InternalServer::aliasPath($this->tmpDir, $path);

		if( !file_put_contents($aliasPath, $ref) ) {
			throw new \RuntimeException('Failed to store path alias');
		}

		return $this->getServerRoot() . $path;
	}

	/**
	 * Override the default server response, e.g. Fallback or 404
	 */
	public function setDefaultResponse( ResponseInterface $response ) : void {
		InternalServer::storeDefaultResponse($this->tmpDir, $response);
	}

	/**
	 * @internal
	 */
	private function getTmpDir() : string {
		$tmpDir = sys_get_temp_dir() ?: '/tmp';
		if( !is_dir($tmpDir) || !is_writable($tmpDir) ) {
			throw new \RuntimeException('Unable to find system tmp directory');
		}

		$tmpPath = $tmpDir . DIRECTORY_SEPARATOR . 'MockWebServer';
		if( !is_dir($tmpPath) ) {
			if( !mkdir($tmpPath) && !is_dir($tmpPath) ) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $tmpPath));
			}
		}

		$tmpPath .= DIRECTORY_SEPARATOR . $this->port;
		if( !is_dir($tmpPath) ) {
			if( !mkdir($tmpPath) && !is_dir($tmpPath) ) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $tmpPath));
			}
		}

		$tmpPath .= DIRECTORY_SEPARATOR . md5(microtime(true) . ':' . rand(0, 100000));
		if( !is_dir($tmpPath) ) {
			if( !mkdir($tmpPath) && !is_dir($tmpPath) ) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $tmpPath));
			}
		}

		return $tmpPath;
	}

	/**
	 * Get the previous requests associated request data.
	 */
	public function getLastRequest() : ?RequestInfo {
		$path = $this->tmpDir . DIRECTORY_SEPARATOR . self::LAST_REQUEST_FILE;
		if( file_exists($path) ) {
			$content = file_get_contents($path);
			if( $content === false ) {
				throw new RuntimeException('failed to read last request');
			}

			$data    = @unserialize($content);
			if( $data instanceof RequestInfo ) {
				return $data;
			}
		}

		return null;
	}

	/**
	 * Get request by offset
	 *
	 * If offset is non-negative, the request will be the index from the start of the server.
	 * If offset is negative, the request will be that from the end of the requests.
	 */
	public function getRequestByOffset( int $offset ) : ?RequestInfo {
		$reqs = glob($this->tmpDir . DIRECTORY_SEPARATOR . 'request.*') ?: [];
		natsort($reqs);

		$item = array_slice($reqs, $offset, 1);
		if( !$item ) {
			return null;
		}

		$path = reset($item);
		if( !$path ) {
			return null;
		}

		$content = file_get_contents($path);
		if( $content === false ) {
			throw new RuntimeException("failed to read request from '{$path}'");
		}

		$data = @unserialize($content);
		if( $data instanceof RequestInfo ) {
			return $data;
		}

		return null;
	}

	/**
	 * Get the host of the server.
	 */
	public function getHost() : string {
		return $this->host;
	}

	/**
	 * Get the port the network server is to be ran on.
	 */
	public function getPort() : int {
		return $this->port;
	}

	/**
	 * Let the OS find an open port for you.
	 */
	private function findOpenPort() : int {
		$sock = socket_create(AF_INET, SOCK_STREAM, 0);
		if( $sock === false ) {
			throw new RuntimeException('Failed to create socket');
		}

		// Bind the socket to an address/port
		if( !socket_bind($sock, $this->getHost(), 0) ) {
			throw new RuntimeException('Could not bind to address');
		}

		socket_getsockname($sock, $checkAddress, $checkPort);
		socket_close($sock);

		if( $checkPort > 0 ) {
			return $checkPort;
		}

		throw new RuntimeException('Failed to find open port');
	}

	private function isWindowsPlatform() : bool {
		return defined('PHP_WINDOWS_VERSION_MAJOR');
	}

	/**
	 * Create the appropriate process runner for the current platform
	 */
	private function createProcessRunner() : ProcessRunnerInterface {
		if( $this->isWindowsPlatform() ) {
			return new ProcessRunners\WindowsProcessRunner;
		}

		return new ProcessRunners\PosixProcessRunner;
	}

}
