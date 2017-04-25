<?php

require __DIR__ . '/../vendor/autoload.php';

$server = new \donatj\MockWebServer\MockWebServer;
$server->start();

$url = $server->getServerRoot() . '/endpoint?get=foobar';

echo "Requesting: $url\n\n";
echo file_get_contents($url);