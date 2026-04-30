<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\Exceptions\ServerException;
use donatj\MockWebServer\ProcessRunnerInterface;

/**
 * Abstract base for platform-specific process runners.
 *
 * Provides shared temporary-file tracking and cleanup logic.
 */
abstract class AbstractProcessRunner implements ProcessRunnerInterface {

	public const STDOUT_PREFIX = 'MockWebServer.stdout';
	public const STDERR_PREFIX = 'MockWebServer.stderr';

	/** @var string[] */
	protected $tempFiles = [];

	/** @var resource|null */
	protected $process;

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

		$this->cleanup();
	}

	public function cleanup() : void {
		$this->cleanupTempFiles();
	}

	protected function cleanupTempFiles() : void {
		foreach( $this->tempFiles as $file ) {
			@unlink($file);
		}

		$this->tempFiles = [];
	}

}
