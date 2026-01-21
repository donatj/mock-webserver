<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\RuntimeException;
use donatj\MockWebServer\Exceptions\ServerException;
use donatj\MockWebServer\ProcessRunnerInterface;

/**
 * Process runner for Windows systems
 */
class WindowsProcessRunner implements ProcessRunnerInterface {

	/**
	 * @return resource
	 */
	public function startProcess( string $command, array $env = [] ) {
		// Windows doesn't need the 'exec' prefix

		$stdoutf = tempnam(sys_get_temp_dir(), 'MockWebServer.stdout');
		if( $stdoutf === false ) {
			throw new RuntimeException('error creating stdout temp file');
		}

		$stderrf = tempnam(sys_get_temp_dir(), 'MockWebServer.stderr');
		if( $stderrf === false ) {
			throw new RuntimeException('error creating stderr temp file');
		}

		// On Windows, bypass_shell=true with file resource handles causes "nonexistent pipe" errors.
		// We need to use pipe specifications instead of file resources.
		$descriptorSpec = [
			0 => [ 'pipe', 'r' ],  // stdin
			1 => [ 'file', $stdoutf, 'a' ],  // stdout
			2 => [ 'file', $stderrf, 'a' ],  // stderr
		];

		$pipes = [];
		$process = proc_open($command, $descriptorSpec, $pipes, null, $env, [
			'suppress_errors' => false,
			'bypass_shell'    => false,
		]);

		if( $process === false ) {
			throw new ServerException('Error starting server');
		}

		// On Windows, we need to close the stdin pipe that was created
		if( isset($pipes[0]) ) {
			fclose($pipes[0]);
		}

		return $process;
	}

	public function cleanup() : void {
		// Windows uses array specs, not resources, so no cleanup needed
	}

}
