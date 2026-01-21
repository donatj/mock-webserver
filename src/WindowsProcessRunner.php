<?php

namespace donatj\MockWebServer;

/**
 * Process runner for Windows systems
 *
 * @internal
 */
class WindowsProcessRunner implements ProcessRunner {

	public function prepareCommand( string $command ) : string {
		// Windows doesn't need the 'exec' prefix
		return $command;
	}

	public function buildDescriptorSpec( string $stdoutPath, string $stderrPath ) : array {
		// On Windows, bypass_shell=true with file resource handles causes "nonexistent pipe" errors.
		// We need to use pipe specifications instead of file resources.
		return [
			0 => [ 'pipe', 'r' ],  // stdin
			1 => [ 'file', $stdoutPath, 'a' ],  // stdout
			2 => [ 'file', $stderrPath, 'a' ],  // stderr
		];
	}

	public function getBypassShell() : bool {
		return false;
	}

	public function postProcessSetup( array $descriptorSpec, array $pipes ) : void {
		// On Windows, we need to close the stdin pipe that was created
		if( isset($pipes[0]) ) {
			fclose($pipes[0]);
		}
	}

	public function cleanup() : void {
		// Windows uses array specs, not resources, so no cleanup needed
	}

}
