<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\ServerException;
use donatj\MockWebServer\Exceptions\TempFileException;
use donatj\MockWebServer\ProcessRunnerInterface;

/**
 * Abstract base for platform-specific process runners.
 *
 * Provides shared temporary-file tracking and cleanup logic.
 */
abstract class AbstractProcessRunner implements ProcessRunnerInterface {

	public const STDOUT_PREFIX = 'MockWebServer.stdout';
	public const STDERR_PREFIX = 'MockWebServer.stderr';

	/** @var resource|null */
	protected $process;

	/**
	 * Ensure the process is stopped when the object is destroyed
	 */
	public function __destruct() {
		$this->stop();
	}

	public function isRunning() : bool {
		if( !is_resource($this->process) ) {
			return false;
		}

		$processStatus = proc_get_status($this->process);

		if( !$processStatus ) {
			return false;
		}

		return $processStatus['running'];
	}

	public function stop() : void {
		if( $this->isRunning() ) {
			proc_terminate($this->process);

			$attempts = 0;
			while( $this->isRunning() ) {
				if( ++$attempts > 1000 ) {
					throw new ServerException('Failed to stop server.');
				}

				usleep(10000);
			}
		}
	}

	/**
	 * Create a temporary file
	 *
	 * @param string $prefix Prefix for the temp file name
	 * @throws TempFileException If temp file creation fails
	 * @return string Path to the created temp file
	 */
	protected function createTempFile( string $prefix ) : string {
		$tempFile = tempnam(sys_get_temp_dir(), $prefix);
		if( $tempFile === false ) {
			throw new TempFileException("error creating temp file with prefix '{$prefix}'");
		}

		return $tempFile;
	}

}
