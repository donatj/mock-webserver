<?php

namespace donatj\MockWebServer;

interface InitializingResponseInterface extends ResponseInterface {

	/**
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @return void
	 */
	public function initialize( RequestInfo $request );

}
