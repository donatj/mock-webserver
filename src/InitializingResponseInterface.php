<?php

namespace donatj\MockWebServer;

/**
 * InitializingResponseInterface is used to initialize a response before headers are sent.
 */
interface InitializingResponseInterface extends ResponseInterface {

	/**
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @internal
	 */
	public function initialize( RequestInfo $request ) : void;

}
