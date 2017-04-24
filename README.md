# Mock Web Server

Simple mock webserver in PHP for unit testing.

Unit testing HTTP requests can be difficult, especially in cases where injecting a request library is diffult or not ideal.

Mock Web Server creates a local Web Server you can make predefined requests against.  

## Example:

```php
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

// $http_response_header is magically defined in scope
//  by file_get_contents with the response headers
echo implode("\n", $http_response_header) . "\n\n";
echo $content . "\n";
```

Outputs:

```
Requesting: http://127.0.0.1:8123/VND.DonatStudios.MockWebServer/9acece3eac841f003f4258e0e00445ed

HTTP/1.0 200 OK
Host: 127.0.0.1:8123
Connection: close
X-Powered-By: PHP/5.6.30
X-Hot-Sauce: foobar
Content-type: text/html; charset=UTF-8

{"foo":"bar"}
```

## Installing

```php
composer require "donatj/mock-webserver" --dev
```

Omitting the `--dev` will add this to `require` rather than `require-dev`
