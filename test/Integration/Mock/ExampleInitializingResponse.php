<?php

namespace Test\Integration\Mock;

use donatj\MockWebServer\InitializingResponseInterface;
use donatj\MockWebServer\RequestInfo;
use donatj\MockWebServer\Response;

class ExampleInitializingResponse extends Response implements InitializingResponseInterface {

	public function __construct() {
		parent::__construct('Initializing Response');
	}

	public function initialize( RequestInfo $request ) : void {
		$this->headers['X-Did-Call-Init'] = 'YES';
	}

}
