<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

$config = json_encode((object) [
   '/foo/bar' => (object) [
       'GET' => (object) ['body' => 'Bar!', 'headers' => ['X-Foo-Bar' => 'Baz'], 'status' => 200],
   ]
]);

$server->load($config);

$url = $server->getServerRoot() . '/foo/bar';

echo "Requesting: $url\n\n";

$content = file_get_contents($url);

echo implode("\n", $http_response_header) . "\n\n";
echo $content . "\n";