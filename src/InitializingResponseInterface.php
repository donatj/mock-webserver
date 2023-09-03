<?php

namespace donatj\MockWebServer;

/**
 * InitializingResponseInterface is used to initialize a response before headers are sent.
 */
interface InitializingResponseInterface extends ResponseInterface {

	/**
	 * @internal
	 */
	public function initialize( RequestInfo $request ) : void;

}
