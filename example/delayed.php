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
$delayedResponse = new DelayedResponse(
	$response,
	100000 // sets a delay of 100000 microseconds (.1 seconds) before returning the response
);

$realtimeUrl = $server->setResponseOfPath('/realtime', $response);
$delayedUrl  = $server->setResponseOfPath('/delayed', $delayedResponse);

echo "Requesting: $realtimeUrl\n\n";

// This request will run as quickly as possible
$start = microtime(true);
file_get_contents($realtimeUrl);
echo "Realtime Request took: " . (microtime(true) - $start) . " seconds\n\n";

echo "Requesting: $delayedUrl\n\n";

// The request will take the delayed time + the time it takes to make and transfer the request
$start = microtime(true);
file_get_contents($delayedUrl);
echo "Delayed Request took: " . (microtime(true) - $start) . " seconds\n\n";
