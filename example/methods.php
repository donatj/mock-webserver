<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseByMethod;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// Create a response for both a POST and GET request to the same URL

$response = new ResponseByMethod([
	ResponseByMethod::METHOD_GET  => new Response("This is our http GET response"),
	ResponseByMethod::METHOD_POST => new Response("This is our http POST response", [], 201),
]);

$url = $server->setResponseOfPath('/foo/bar', $response);

foreach( [ ResponseByMethod::METHOD_GET, ResponseByMethod::METHOD_POST ] as $method ) {
	echo "$method request to $url:\n";

	$context = stream_context_create([ 'http' => [ 'method' => $method ] ]);
	$content = file_get_contents($url, false, $context);

	echo $content . "\n\n";
}
