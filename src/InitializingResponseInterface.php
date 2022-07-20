<?php

namespace donatj\MockWebServer;

interface InitializingResponseInterface extends ResponseInterface {

	/**
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @return void
	 * @internal
	 */
	public function initialize( RequestInfo $request );

}
