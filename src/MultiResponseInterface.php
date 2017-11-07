<?php

namespace donatj\MockWebServer;

interface MultiResponseInterface extends ResponseInterface {

	/**
	 * @return bool
	 */
	public function next();

}
