<?php

namespace donatj\MockWebServer;

/**
 * Interface for platform-specific process execution
 *
 * @internal
 */
interface ProcessRunner {

	/**
	 * Start a process with the given command
	 *
	 * @param string $command The command to execute
	 * @return resource The process resource
	 * @throws \donatj\MockWebServer\Exceptions\ServerException If the process fails to start
	 */
	public function startProcess( string $command );

	/**
	 * Clean up resources when stopping the process
	 */
	public function cleanup() : void;

}
