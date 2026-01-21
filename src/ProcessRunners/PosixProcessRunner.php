<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\RuntimeException;
use donatj\MockWebServer\Exceptions\ServerException;
use donatj\MockWebServer\ProcessRunnerInterface;

/**
 * Process runner for POSIX (Unix/Linux/macOS) systems
 *
 * @internal
 */
class PosixProcessRunner implements ProcessRunnerInterface {

	/** @var resource[] */
	private $descriptors = [];

	/**
	 * @return resource
	 */
	public function startProcess( string $command, array $env = [] ) {
		// We need to prefix exec to get the correct process
		// http://php.net/manual/ru/function.proc-get-status.php#93382
		$command = 'exec ' . $command;

		$stdoutf = tempnam(sys_get_temp_dir(), 'MockWebServer.stdout');
		if( $stdoutf === false ) {
			throw new RuntimeException('error creating stdout temp file');
		}

		$stderrf = tempnam(sys_get_temp_dir(), 'MockWebServer.stderr');
		if( $stderrf === false ) {
			throw new RuntimeException('error creating stderr temp file');
		}

		$stdin = fopen('php://stdin', 'rb');
		if( $stdin === false ) {
			throw new RuntimeException('error opening stdin');
		}

		$stdout = fopen($stdoutf, 'ab');
		if( $stdout === false ) {
			throw new RuntimeException('error opening stdout');
		}

		$stderr = fopen($stderrf, 'ab');
		if( $stderr === false ) {
			throw new RuntimeException('error opening stderr');
		}

		$descriptorSpec = [ $stdin, $stdout, $stderr ];

		$pipes = [];
		$process = proc_open($command, $descriptorSpec, $pipes, null, $env, [
			'suppress_errors' => false,
			'bypass_shell'    => true,
		]);

		if( $process === false ) {
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
	}

}
