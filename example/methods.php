<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\RequestInfo;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// Create a response for both a POST and GET request to the same URL:
$methods = [RequestInfo::METHOD_GET, RequestInfo::METHOD_POST];

foreach ($methods as $method)
{
    $url = $server->setResponseOfPath('/foo/bar', new Response("This is our http $method response"), $method);

    echo "$method request to $url:\n";

    $context = stream_context_create(['http' => ['method'  => $method]]);
    $content = file_get_contents($url, false, $context);

    echo $content . "\n\n";
}
