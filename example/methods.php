<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\RequestInfo;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// Create a response for both a POST and GET request to the same URL:
$urls = [];
$urls['GET']  = $server->setResponseOfPath('/foo/bar', new Response('This is our http GET response'), RequestInfo::GET);
$urls['POST'] = $server->setResponseOfPath('/foo/bar', new Response('This is our http POST response'), RequestInfo::POST);

foreach (['GET', 'POST'] as $method)
{
    $url = $urls[$method];

    echo "$method request to $url:\n";

    $context = stream_context_create(['http' => ['method'  => $method]]);
    $content = file_get_contents($url, false, $context);

    echo $content . "\n\n";
}
