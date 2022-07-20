<?php

use donatj\MockWebServer\DelayedResponse;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

$response = new Response(
	'This is our http body response',
	[ 'Cache-Control' => 'no-cache' ],
	200
);

// Wrap the response in a DelayedResponse object, which will delay the response
$delay = new DelayedResponse(
	$response,
	100000 // sets a delay of 100000 microseconds (.1 seconds) before returning the response
);

$url = $server->setResponseOfPath('/delayedPath', $delay);

echo "Requesting: $url\n\n";

// The request will take the delayed time + the time it takes to make and transfer the request
$content = file_get_contents($url);
