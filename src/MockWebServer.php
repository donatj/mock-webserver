<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions;

class MockWebServer {

	const VND = 'VND.DonatStudios.MockWebServer';

	const RESPONSE_BODY    = 'body';
	const RESPONSE_STATUS  = 'status';
	const RESPONSE_HEADERS = 'headers';

	const TMP_ENV = 'MOCK_WEB_SERVER_TMP';

	protected $pid = null;

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * Indicates whether or not the server was successfully started
	 *
	 * @var bool
	 */
	private $started = false;

	/**
	 * @var string
	 */
	private $tmpDir;

	/**
	 * TestWebServer constructor.
	 *
	 * @param int    $port Network port to run on
	 * @param string $host Listening hostname
	 */
	public function __construct( $port = 8123, $host = "127.0.0.1" ) {
		$this->port   = $port;
		$this->host   = $host;
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
		$cmd    = "php -S {$this->host}:{$this->port} " . escapeshellarg($script);

		if( !putenv(self::TMP_ENV . '=' . $this->tmpDir) ) {
			throw new Exceptions\RuntimeException('Unable to put environmental variable');
		}
		$fullCmd = sprintf("%s > %s 2>&1 & echo $!",
			escapeshellcmd($cmd),
			escapeshellarg($stdout)
		);

		$this->pid = exec(
			$fullCmd,
			$o,
			$ret
		);

		if( !ctype_digit($this->pid) ) {
			throw new Exceptions\ServerException("Error starting server, received '{$this->pid}', expected int PID");
		}

		sleep(1); // just to make sure it's fully started up, maybe not necessary

		if( !$this->isRunning() ) {
			throw new Exceptions\ServerException("Failed to start server. Is something already running on port {$this->port}?");
		}

		$this->started = true;

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
		if( !$this->pid ) {
			return false;
		}

		$result = shell_exec(sprintf("ps %d",
			$this->pid));
		if( count(preg_split("/\n/", $result)) > 2 ) {
			return true;
		}

		return false;
	}

	/**
	 * Stop the Web Server
	 */
	public function stop() {
		if( $this->started ) {
			exec(sprintf('kill %d',
				$this->pid));
		}

		$this->started = false;
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
	 * @param string $body
	 * @param array  $headers
	 * @param int    $status
	 * @return string URL where response can be found
	 */
	public function getUrlOfResponse( $body, array $headers = [], $status = 200 ) {
		$ref = $this->storeResponse($body, $headers, $status);

		return $this->getServerRoot() . '/' . self::VND . '/' . $ref;
	}

	/**
	 * Set a specified path to provide a specific response
	 *
	 * @param string $path
	 * @param string $body
	 * @param array  $headers
	 * @param int    $status
	 * @return string
	 */
	public function setResponseOfPath( $path, $body, array $headers = [], $status = 200 ) {
		$ref = $this->storeResponse($body, $headers, $status);

		$path  = '/' . ltrim($path, '/');
		$alias = 'alias.' . md5($path);

		if( !file_put_contents($this->tmpDir . DIRECTORY_SEPARATOR . $alias, $ref) ) {
			throw new \RuntimeException('Failed to store path alias');
		}

		return $this->getServerRoot() . $path;
	}

	/**
	 * @param string $body
	 * @param array  $headers
	 * @param int    $status
	 * @return string
	 */
	private function storeResponse( $body, array $headers, $status ) {
		$content = json_encode([
			self::RESPONSE_BODY    => $body,
			self::RESPONSE_STATUS  => $status,
			self::RESPONSE_HEADERS => $headers,
		]);

		$ref = md5($content);

		if( !file_put_contents($this->tmpDir . DIRECTORY_SEPARATOR . $ref, $content) ) {
			throw new Exceptions\RuntimeException('Failed to write temporary content');
		}

		return $ref;
	}

	/**
	 * @return string
	 * @internal
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

		$tmpPath .= DIRECTORY_SEPARATOR . md5(microtime() . ':' . rand(0, 100000));
		if( !is_dir($tmpPath) ) {
			mkdir($tmpPath);
		}

		return $tmpPath;
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
}
