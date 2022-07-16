<?php

namespace donatj\MockWebServer;

interface InitializingResponse extends ResponseInterface {

	/**
	 * @param \donatj\MockWebServer\RequestInfo $request
	 * @return void
	 */
	public function initialize( RequestInfo $request );

}
