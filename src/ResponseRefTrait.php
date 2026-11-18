<?php

namespace donatj\MockWebServer;

/**
 * Provides a stable, unique reference for a response instance.
 *
 * @internal
 */
trait ResponseRefTrait {

	/** @var string */
	private $responseRef;

	private function initializeResponseRef() : void {
		$this->responseRef = bin2hex(random_bytes(16));
	}

	public function getRef() : string {
		return $this->responseRef;
	}

}
