<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\ServerException;

/**
 * Process runner for Windows systems
 */
class WindowsProcessRunner extends AbstractProcessRunner {

	/**
	 * @return resource
	 */
	public function startProcess( string $phpBinary, string $host, int $port, string $script, array $env = [] ) {
		$command = sprintf('%s -S %s:%d %s',
			escapeshellarg($phpBinary),
			escapeshellarg($host),
			$port,
			escapeshellarg($script)
		);

		$stdoutf = $this->createTempFile(self::STDOUT_PREFIX);
		$stderrf = $this->createTempFile(self::STDERR_PREFIX);

		// On Windows with bypass_shell enabled, proc_open expects array-based descriptor
		// specifications rather than resource handles to avoid "nonexistent pipe" errors.
		$descriptorSpec = [
			0 => [ 'pipe', 'r' ],  // stdin
			1 => [ 'file', $stdoutf, 'a' ],  // stdout
			2 => [ 'file', $stderrf, 'a' ],  // stderr
		];

		// Merge with parent environment to ensure PATH, SystemRoot, ComSpec, etc. are present
		$mergedEnv = array_merge(getenv() ?: [], $env);

		$pipes = [];
		$this->process = proc_open($command, $descriptorSpec, $pipes, null, $mergedEnv, [
			'suppress_errors' => false,
			'bypass_shell'    => true,
		]);

		if( $this->process === false ) {
			throw new ServerException('Error starting server');
		}

		// On Windows, we need to close the stdin pipe that was created
		if( isset($pipes[0]) ) {
			fclose($pipes[0]);
		}

		return $this->process;
	}

}
