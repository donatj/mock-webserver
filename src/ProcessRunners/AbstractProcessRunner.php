<?php

namespace donatj\MockWebServer\ProcessRunners;

use donatj\MockWebServer\ProcessRunnerInterface;

/**
 * Abstract base for platform-specific process runners.
 *
 * Provides shared temporary-file tracking and cleanup logic.
 */
abstract class AbstractProcessRunner implements ProcessRunnerInterface {

	const STDOUT_PREFIX = 'MockWebServer.stdout';
	const STDERR_PREFIX = 'MockWebServer.stderr';

	/** @var string[] */
	protected $tempFiles = [];

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
