<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\RuntimeException;
use donatj\MockWebServer\Exceptions\ServerException;
use donatj\MockWebServer\ProcessRunnerInterface;

/**
 * Process runner for POSIX (Unix/Linux/macOS) systems
 */
class PosixProcessRunner implements ProcessRunnerInterface {

	/** @var resource[] */
	private $descriptors = [];

	/** @var string[] */
	private $tempFiles = [];

	/**
	 * @return resource
	 */
	public function startProcess( string $phpBinary, string $host, int $port, string $script, array $env = [] ) {
		// We need to prefix exec to get the correct process
		// http://php.net/manual/ru/function.proc-get-status.php#93382
		$command = sprintf('exec %s -S %s:%d %s',
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

		$stdin = fopen('php://stdin', 'rb');
		if( $stdin === false ) {
			$this->cleanupTempFiles();
			throw new RuntimeException('error opening stdin');
		}

		$stdout = fopen($stdoutf, 'ab');
		if( $stdout === false ) {
			fclose($stdin);
			$this->cleanupTempFiles();
			throw new RuntimeException('error opening stdout');
		}

		$stderr = fopen($stderrf, 'ab');
		if( $stderr === false ) {
			fclose($stdin);
			fclose($stdout);
			$this->cleanupTempFiles();
			throw new RuntimeException('error opening stderr');
		}

		$descriptorSpec = [ $stdin, $stdout, $stderr ];

		// Merge with parent environment to ensure PATH and other required vars are present
		// Filter out non-string values that can't be passed to proc_open
		$parentEnv = array_filter($_SERVER, 'is_string');
		$mergedEnv = array_merge($parentEnv, $env);

		$pipes = [];
		$process = proc_open($command, $descriptorSpec, $pipes, null, $mergedEnv, [
			'suppress_errors' => false,
			'bypass_shell'    => true,
		]);

		if( $process === false ) {
			fclose($stdin);
			fclose($stdout);
			fclose($stderr);
			$this->cleanupTempFiles();
			throw new ServerException('Error starting server');
		}

		// Store the descriptors for cleanup
		$this->descriptors = $descriptorSpec;

		return $process;
	}

	public function cleanup() : void {
		foreach( $this->descriptors as $descriptor ) {
			if( is_resource($descriptor) ) {
				@fclose($descriptor);
			}
		}

		$this->descriptors = [];
		$this->cleanupTempFiles();
	}

	private function cleanupTempFiles() : void {
		foreach( $this->tempFiles as $file ) {
			@unlink($file);
		}

		$this->tempFiles = [];
	}

}
