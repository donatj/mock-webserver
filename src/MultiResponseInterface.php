<?php

namespace donatj\MockWebServer;

/**
 * MultiResponseInterface is used to vary the response to a request.
 */
interface MultiResponseInterface extends ResponseInterface {

	/**
	 * Called after each request is sent
	 *
	 * @internal
	 */
	public function next() : bool;

}
