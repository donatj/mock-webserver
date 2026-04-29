<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\RuntimeException;
use donatj\MockWebServer\Exceptions\ServerException;
use donatj\MockWebServer\ProcessRunnerInterface;

/**
 * Process runner for Windows systems
 */
class WindowsProcessRunner implements ProcessRunnerInterface {

	/** @var string[] */
	private $tempFiles = [];

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
		$stdoutf = tempnam(sys_get_temp_dir(), 'MockWebServer.stdout');
		if( $stdoutf === false ) {
			throw new RuntimeException('error creating stdout temp file');
		}

		$this->tempFiles[] = $stdoutf;

		$stderrf = tempnam(sys_get_temp_dir(), 'MockWebServer.stderr');
		if( $stderrf === false ) {
			@unlink($stdoutf);
			throw new RuntimeException('error creating stderr temp file');
		}

		$this->tempFiles[] = $stderrf;

		// On Windows with bypass_shell enabled, proc_open expects array-based descriptor
		// specifications rather than resource handles to avoid "nonexistent pipe" errors.
		$descriptorSpec = [
			0 => [ 'pipe', 'r' ],  // stdin
			1 => [ 'file', $stdoutf, 'a' ],  // stdout
			2 => [ 'file', $stderrf, 'a' ],  // stderr
		];

		// Merge with parent environment to ensure PATH, SystemRoot, ComSpec, etc. are present
		// Filter out non-string values that can't be passed to proc_open
		$parentEnv = array_filter($_SERVER, 'is_string');
		$mergedEnv = array_merge($parentEnv, $env);

		$pipes = [];
		$process = proc_open($command, $descriptorSpec, $pipes, null, $mergedEnv, [
			'suppress_errors' => false,
			'bypass_shell'    => true,
		]);

		if( $process === false ) {
			$this->cleanupTempFiles();
			throw new ServerException('Error starting server');
		}

		// On Windows, we need to close the stdin pipe that was created
		if( isset($pipes[0]) ) {
			fclose($pipes[0]);
		}

		return $process;
	}

	public function cleanup() : void {
		$this->cleanupTempFiles();
	}

	private function cleanupTempFiles() : void {
		foreach( $this->tempFiles as $file ) {
			@unlink($file);
		}

		$this->tempFiles = [];
	}

}
