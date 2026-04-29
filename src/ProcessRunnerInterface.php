<?php

namespace donatj\MockWebServer;

/**
 * Interface for platform-specific process execution
 */
interface ProcessRunnerInterface {

	/**
	 * Start a process with the given command
	 *
	 * @param string $command The command to execute
	 * @param array<string,string> $env Environment variables to pass to the process
	 * @return resource The process resource
	 * @throws \donatj\MockWebServer\Exceptions\ServerException If the process fails to start
	 * @throws \donatj\MockWebServer\Exceptions\RuntimeException If temp file or stream operations fail
	 */
	public function startProcess( string $command, array $env = [] );

	/**
	 * Clean up resources when stopping the process
	 */
	public function cleanup() : void;

}
