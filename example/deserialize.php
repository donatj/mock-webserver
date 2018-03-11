<?php

use donatj\MockWebServer\MockWebServer;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

$config = json_encode((object) [
   '/foo/bar' => (object) [
       'GET' => [
           (object) ['body' => (object) ['foo' => 'bar'], 'headers' => ['X-Foo-Bar' => 'Baz'], 'status' => 200],
           (object) ['body' => '', 'headers' => ['X-Foo-Bar' => 'Baz Baz'], 'status' => 204]
       ]
   ]
]);

$server->load($config);

$url = $server->getServerRoot() . '/foo/bar';

echo "Requesting: $url\n\n";

for ($i=0; $i < 2; $i++)
{
    $content = file_get_contents($url);

    echo implode("\n", $http_response_header) . "\n\n";
    echo $content . "\n";
}
