<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// We define the servers response to requests of the /definedPath endpoint
$url = $server->setResponseOfPath(
	'/definedPath',
	new ResponseStack(
		new Response("Response One"),
		new Response("Response Two")
	)
);

echo "Requesting: $url\n\n";

$contentOne = file_get_contents($url);
$contentTwo = file_get_contents($url);
// This third request is expected to 404 which will error if errors are not ignored
$contentThree = file_get_contents($url, false, stream_context_create([ 'http' => [ 'ignore_errors' => true ] ]));

// $http_response_header is a little known variable magically defined
// in the current scope by file_get_contents with the response headers
echo $contentOne . "\n";
echo $contentTwo . "\n";
echo $contentThree . "\n";
