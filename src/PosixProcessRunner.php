<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\RuntimeException;

/**
 * Process runner for POSIX (Unix/Linux/macOS) systems
 *
 * @internal
 */
class PosixProcessRunner implements ProcessRunner {

	/** @var resource[] */
	private $descriptors = [];

	public function prepareCommand( string $command ) : string {
		// We need to prefix exec to get the correct process
		// http://php.net/manual/ru/function.proc-get-status.php#93382
		return 'exec ' . $command;
	}

	public function buildDescriptorSpec( string $stdoutPath, string $stderrPath ) : array {
		$stdin = fopen('php://stdin', 'rb');
		if( $stdin === false ) {
			throw new RuntimeException('error opening stdin');
		}

		$stdout = fopen($stdoutPath, 'ab');
		if( $stdout === false ) {
			throw new RuntimeException('error opening stdout');
		}

		$stderr = fopen($stderrPath, 'ab');
		if( $stderr === false ) {
			throw new RuntimeException('error opening stderr');
		}

		return [ $stdin, $stdout, $stderr ];
	}

	public function getBypassShell() : bool {
		return true;
	}

	public function postProcessSetup( array $descriptorSpec, array $pipes ) : void {
		// Store the descriptors for cleanup
		$this->descriptors = $descriptorSpec;
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
