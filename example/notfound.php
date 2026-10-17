<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Responses\NotFoundResponse;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// The default response is donatj\MockWebServer\Responses\DefaultResponse
// which returns an HTTP 200 and a descriptive JSON payload.
//
// Change the default response to donatj\MockWebServer\Responses\NotFoundResponse
// to get a standard 404.
//
// Any other response may be specified as default as well.
$server->setDefaultResponse(new NotFoundResponse);

$content = file_get_contents($server->getServerRoot() . '/PageDoesNotExist', false, stream_context_create([
	'http' => [ 'ignore_errors' => true ], // allow reading 404s
]));

// PHP 8.3 and earlier should use $http_response_header here.
$headers = http_get_last_response_headers();

echo implode("\n", $headers) . "\n\n";
echo $content . "\n";
