<?php

namespace donatj\MockWebServer;

/**
 * Interface for platform-specific process execution
 */
interface ProcessRunnerInterface {

	/**
	 * Start a PHP built-in server process
	 *
	 * @param string               $phpBinary Path to the PHP binary
	 * @param string               $host      Hostname to listen on
	 * @param int                  $port      Port to listen on
	 * @param string               $script    Path to the server script
	 * @param array<string,string> $env       Environment variables to pass to the process
	 * @throws \donatj\MockWebServer\Exceptions\ServerException If the process fails to start
	 * @throws \donatj\MockWebServer\Exceptions\RuntimeException If temp file or stream operations fail
	 * @return resource The process resource
	 */
	public function startProcess( string $phpBinary, string $host, int $port, string $script, array $env = [] );

	/**
	 * Is the process currently running?
	 */
	public function isRunning() : bool;

	/**
	 * Stop the running process
	 *
	 * @throws \donatj\MockWebServer\Exceptions\ServerException If the process fails to stop
	 */
	public function stop() : void;

}
