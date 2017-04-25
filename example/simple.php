<?php

require 'vendor/autoload.php';

$server = new \donatj\MockWebServer\MockWebServer;
$server->start();

// Get us a generated URL that will give us the defined request.
$url = $server->getUrlOfResponse(
	json_encode([ 'foo' => 'bar' ]),
	[ 'X-Hot-Sauce' => 'foobar' ],
	200
);

echo "Requesting: $url\n\n";

$content = file_get_contents($url);

// $http_response_header is a little known variable magically defined
// in the current scope by file_get_contents with the response headers
echo implode("\n", $http_response_header) . "\n\n";
echo $content . "\n";