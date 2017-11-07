<?php

namespace donatj\MockWebServer;

interface MultiResponseInterface extends ResponseInterface {

	/**
	 * @internal
	 * @return bool
	 */
	public function next();

}
