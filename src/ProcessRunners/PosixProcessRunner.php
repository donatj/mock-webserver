<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\RuntimeException;
use donatj\MockWebServer\Exceptions\ServerException;

/**
 * Process runner for POSIX (Unix/Linux/macOS) systems
 */
class PosixProcessRunner extends AbstractProcessRunner {

	/** @var resource[] */
	private $descriptors = [];

	/**
	 * @return resource
	 */
	public function startProcess( string $phpBinary, string $host, int $port, string $script, array $env = [] ) {
		// Clean up any leftover descriptors from previous run
		$this->closeDescriptors();

		// We need to prefix exec to get the correct process
		// http://php.net/manual/en/function.proc-get-status.php#93382
		$command = sprintf('exec %s -S %s:%d %s',
			escapeshellarg($phpBinary),
			escapeshellarg($host),
			$port,
			escapeshellarg($script)
		);

		$stdoutf = $this->createTempFile(self::STDOUT_PREFIX);
		$stderrf = $this->createTempFile(self::STDERR_PREFIX);

		$stdin = fopen('php://stdin', 'rb');
		if( $stdin === false ) {
			throw new RuntimeException('error opening stdin');
		}

		$stdout = fopen($stdoutf, 'ab');
		if( $stdout === false ) {
			fclose($stdin);

			throw new RuntimeException('error opening stdout');
		}

		$stderr = fopen($stderrf, 'ab');
		if( $stderr === false ) {
			fclose($stdin);
			fclose($stdout);

			throw new RuntimeException('error opening stderr');
		}

		$descriptorSpec = [ $stdin, $stdout, $stderr ];

		// Merge with parent environment to ensure PATH and other required vars are present
		$mergedEnv = array_merge(getenv() ?: [], $env);

		$pipes = [];
		$this->process = proc_open($command, $descriptorSpec, $pipes, null, $mergedEnv, [
			'suppress_errors' => false,
			'bypass_shell'    => true,
		]);

		if( $this->process === false ) {
			fclose($stdin);
			fclose($stdout);
			fclose($stderr);

			throw new ServerException('Error starting server');
		}

		// Store the descriptors for cleanup on stop
		$this->descriptors = $descriptorSpec;

		return $this->process;
	}

	public function stop() : void {
		parent::stop();

		$this->closeDescriptors();
	}

	private function closeDescriptors() : void {
		foreach( $this->descriptors as $descriptor ) {
			if( is_resource($descriptor) ) {
				@fclose($descriptor);
			}
		}

		$this->descriptors = [];
	}

}
