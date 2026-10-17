<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// We define the server's response to requests of the /definedPath endpoint
$url = $server->setResponseOfPath(
	'/definedPath',
	new Response(
		'This is our http body response',
		[ 'Cache-Control' => 'no-cache' ],
		200
	)
);

echo "Requesting: $url\n\n";

$content = file_get_contents($url);

// PHP 8.3 and earlier should use $http_response_header here.
$headers = http_get_last_response_headers();

echo implode("\n", $headers) . "\n\n";
echo $content . "\n";
