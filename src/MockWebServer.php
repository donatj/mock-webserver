<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions;

class MockWebServer {

	const VND = 'VND.DonatStudios.MockWebServer';

	const LAST_REQUEST_FILE  = 'last.request';
	const REQUEST_COUNT_FILE = 'count.request';

	const TMP_ENV = 'MOCK_WEB_SERVER_TMP';

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var string
	 */
	private $tmpDir;

	/**
	 * Contain link to opened process resource
	 *
	 * @var resource
	 */
	private $process;

	/**
	 * TestWebServer constructor.
	 *
	 * @param int    $port Network port to run on
	 * @param string $host Listening hostname
	 */
	public function __construct( $port = 0, $host = '127.0.0.1' ) {
		$this->port = $port;
		$this->host = $host;

		if( $this->port == 0 ) {
			$this->port = $this->findOpenPort();
		}

		$this->tmpDir = $this->getTmpDir();
	}

	/**
	 * Start the Web Server on the selected port and host
	 */
	public function start() {
		if( $this->isRunning() ) {
			return;
		}

		$script = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'server' . DIRECTORY_SEPARATOR . 'server.php';

		$stdout = tempnam(sys_get_temp_dir(), 'mockserv-stdout-');
		$cmd    = "php -S {$this->host}:{$this->port} " . $script;

		if( !putenv(self::TMP_ENV . '=' . $this->tmpDir) ) {
			throw new Exceptions\RuntimeException('Unable to put environmental variable');
		}
		$fullCmd = sprintf('%s > %s 2>&1',
			$cmd,
			$stdout
		);

		InternalServer::incrementRequestCounter($this->tmpDir, 0);

		$this->process = $this->startServer($fullCmd);

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

		register_shutdown_function(function () {
			if( $this->isRunning() ) {
				$this->stop();
			}
		});
	}

	/**
	 * Is the Web Server currently running?
	 *
	 * @return bool
	 */
	public function isRunning() {
		if( !is_resource($this->process) ) {
			return false;
		}

		$processStatus = proc_get_status($this->process);

		if( !$processStatus ) {
			return false;
		}

		return $processStatus['running'];
	}

	/**
	 * Stop the Web Server
	 */
	public function stop() {
		if( $this->isRunning() ) {
			proc_terminate($this->process);

			$attempts = 0;
			while( $this->isRunning() ) {
				if( ++$attempts > 1000 ) {
					throw new Exceptions\ServerException('Failed to stop server.');
				}

				usleep(10000);
			}
		}
	}

	/**
	 * Get the HTTP root of the webserver
	 *  e.g.: http://127.0.0.1:8123
	 *
	 * @return string
	 */
	public function getServerRoot() {
		return "http://{$this->host}:{$this->port}";
	}

	/**
	 * Get a URL providing the specified response.
	 *
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @return string URL where response can be found
	 */
	public function getUrlOfResponse( ResponseInterface $response ) {
		$ref = InternalServer::storeResponse($this->tmpDir, $response);

		return $this->getServerRoot() . '/' . self::VND . '/' . $ref;
	}

	/**
	 * Set a specified path to provide a specific response
	 *
	 * @param string                                  $path
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @return string
	 */
	public function setResponseOfPath( $path, ResponseInterface $response ) {
		$ref = InternalServer::storeResponse($this->tmpDir, $response);

		$aliasPath = InternalServer::aliasPath($this->tmpDir, $path);

		if( !file_put_contents($aliasPath, $ref) ) {
			throw new \RuntimeException('Failed to store path alias');
		}

		return $this->getServerRoot() . $path;
	}

	/**
	 * Override the default server response, e.g. Fallback or 404
	 *
	 * @param \donatj\MockWebServer\ResponseInterface $response
	 * @return void
	 */
	public function setDefaultResponse( ResponseInterface $response ) {
		InternalServer::storeDefaultResponse($this->tmpDir, $response);
	}

	/**
	 * @return string
	 */
	private function getTmpDir() {
		$tmpDir = sys_get_temp_dir() ?: '/tmp';
		if( !is_dir($tmpDir) || !is_writable($tmpDir) ) {
			throw new \RuntimeException('Unable to find system tmp directory');
		}

		$tmpPath = $tmpDir . DIRECTORY_SEPARATOR . 'MockWebServer';
		if( !is_dir($tmpPath) ) {
			mkdir($tmpPath);
		}

		$tmpPath .= DIRECTORY_SEPARATOR . $this->port;
		if( !is_dir($tmpPath) ) {
			mkdir($tmpPath);
		}

		$tmpPath .= DIRECTORY_SEPARATOR . md5(microtime(true) . ':' . rand(0, 100000));
		if( !is_dir($tmpPath) ) {
			mkdir($tmpPath);
		}

		return $tmpPath;
	}

	/**
	 * Get the previous requests associated request data.
	 *
	 * @return RequestInfo|null
	 */
	public function getLastRequest() {
		$path = $this->tmpDir . DIRECTORY_SEPARATOR . self::LAST_REQUEST_FILE;
		if( file_exists($path) ) {
			$content = file_get_contents($path);
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
	 *
	 * @param int $offset
	 * @return RequestInfo|null
	 */
	public function getRequestByOffset( $offset ) {
		$reqs = glob($this->tmpDir . DIRECTORY_SEPARATOR . 'request.*');
		natsort($reqs);

		$item = array_slice($reqs, $offset, 1);
		if( !$item ) {
			return null;
		}

		$path    = reset($item);
		$content = file_get_contents($path);
		$data    = @unserialize($content);
		if( $data instanceof RequestInfo ) {
			return $data;
		}

		return null;
	}

	/**
	 * Get the host of the server.
	 *
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * Get the port the network server is to be ran on.
	 *
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * Let the OS find an open port for you.
	 *
	 * @return int
	 */
	private function findOpenPort() {
		$sock = socket_create(AF_INET, SOCK_STREAM, 0);

		// Bind the socket to an address/port
		if( !socket_bind($sock, $this->getHost(), 0) ) {
			throw new Exceptions\RuntimeException('Could not bind to address');
		}

		socket_getsockname($sock, $checkAddress, $checkPort);
		socket_close($sock);

		if( $checkPort > 0 ) {
			return $checkPort;
		}

		throw new Exceptions\RuntimeException('Failed to find open port');
	}

	/**
	 * @return bool
	 */
	private function isWindowsPlatform() {
		return defined('PHP_WINDOWS_VERSION_MAJOR');
	}

	/**
	 * @param string $fullCmd
	 * @return resource
	 */
	private function startServer( $fullCmd ) {
		if( !$this->isWindowsPlatform() ) {
			// We need to prefix exec to get the correct process http://php.net/manual/ru/function.proc-get-status.php#93382
			$fullCmd = 'exec ' . $fullCmd;
		}

		$pipes = [];
		$env   = null;
		$cwd   = null;

		$process = proc_open($fullCmd, [], $pipes, $cwd, $env, [
			'suppress_errors' => false,
			'bypass_shell'    => true,
		]);

		if( is_resource($process) ) {
			return $process;
		}

		throw new Exceptions\ServerException("Error starting server");
	}
}
