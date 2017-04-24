<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions;

class MockWebServer {

	const VND = 'VND.DonatStudios.MockWebServer';

	const RESPONSE_BODY    = 'body';
	const RESPONSE_STATUS  = 'status';
	const RESPONSE_HEADERS = 'headers';

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
	 * TestWebServer constructor.
	 *
	 * @param int    $port Network port to run on
	 * @param string $host Listening hostname
	 */
	public function __construct( $port = 8123, $host = "127.0.0.1" ) {
		$this->port = $port;
		$this->host = $host;
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
		$content = json_encode([
			self::RESPONSE_BODY    => $body,
			self::RESPONSE_STATUS  => $status,
			self::RESPONSE_HEADERS => $headers,
		]);

		$url     = md5($content);
		$tmpPath = self::getTmpDir();

		if( !file_put_contents($tmpPath . DIRECTORY_SEPARATOR . $url, $content) ) {
			throw new Exceptions\RuntimeException('Failed to write temporary content');
		}

		return $this->getServerRoot() . '/' . self::VND . '/' . $url;
	}

	/**
	 * @return string
	 * @internal
	 */
	public static function getTmpDir() {
		$tmpDir  = sys_get_temp_dir();
		$tmpPath = $tmpDir . DIRECTORY_SEPARATOR . 'MockWebServer';
		if( !is_dir($tmpPath) ) {
			mkdir($tmpPath);
		}

		return $tmpPath;
	}

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}
}