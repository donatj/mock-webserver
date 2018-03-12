<?php

use donatj\MockWebServer\MockWebServer;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

$json = <<<EOF
{
    "/foo/bar": {
        "GET": [
            {
                "body": {
                    "foo": "bar"
                },
                "headers": {
                    "X-Foo-Bar": "Baz"
                },
                "status": 200
            },
            {
                "body": "",
                "headers": {
                    "X-Foo-Bar": "Baz Baz"
                },
                "status": 204
            }
        ]
    },
    "/bar/foo": {
        "POST": {
            "body": {
                "bar": "foo"
            },
            "headers": {
                "X-Bar-Foo": "Foz"
            },
            "status": 200
        }
    }
}
EOF;

$server->load($json);

$url = $server->getServerRoot() . '/foo/bar';

echo "Requesting: $url\n\n";

for ($i=0; $i < 2; $i++)
{
    $content = file_get_contents($url);

    echo implode("\n", $http_response_header) . "\n\n";
    echo $content . "\n";
}

$url = $server->getServerRoot() . '/bar/foo';

echo "Requesting: $url\n\n";

$content = file_get_contents($url);

echo implode("\n", $http_response_header) . "\n\n";
echo $content . "\n";