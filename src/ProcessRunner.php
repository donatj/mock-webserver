<?php

namespace donatj\MockWebServer;

/**
 * Interface for platform-specific process execution
 *
 * @internal
 */
interface ProcessRunner {

	/**
	 * Prepare the command for execution on this platform
	 */
	public function prepareCommand( string $command ) : string;

	/**
	 * Build the descriptor specification for proc_open
	 *
	 * @param string $stdoutPath Path to stdout log file
	 * @param string $stderrPath Path to stderr log file
	 * @return array The descriptor specification for proc_open
	 */
	public function buildDescriptorSpec( string $stdoutPath, string $stderrPath ) : array;

	/**
	 * Get the bypass_shell option value for proc_open
	 */
	public function getBypassShell() : bool;

	/**
	 * Perform any post-process setup after proc_open
	 *
	 * @param array $descriptorSpec The descriptor specification used
	 * @param array $pipes The pipes array from proc_open
	 */
	public function postProcessSetup( array $descriptorSpec, array $pipes ) : void;

	/**
	 * Clean up resources when stopping the process
	 */
	public function cleanup() : void;

}
