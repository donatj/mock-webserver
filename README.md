# Mock Web Server

[![Latest Stable Version](https://poser.pugx.org/donatj/mock-webserver/version)](https://packagist.org/packages/donatj/mock-webserver)
[![License](https://poser.pugx.org/donatj/mock-webserver/license)](https://packagist.org/packages/donatj/mock-webserver)
[![ci.yml](https://github.com/donatj/mock-webserver/actions/workflows/ci.yml/badge.svg)](https://github.com/donatj/mock-webserver/actions/workflows/ci.yml)


Lightweight, easy to use Mock HTTP Server for PHP testing.

Ideal for cases where injecting a mock client isn’t practical, possible, or fully comprehensive.

Mock Web Server serves precise, predefined responses—perfect for testing HTTP clients, webhooks, and API integrations. Useful for testing error handling, timeouts, and edge cases.

Works seamlessly with PHPUnit and other test frameworks, with minimal setup.


## Documentation

[See: docs/docs.md](docs/docs.md)

## Requirements

- **php**: >=7.1
- **ext-sockets**: *
- **ext-json**: *
- **ralouphie/getallheaders**: ~2.0 || ~3.0

## Installing

Install the latest version with:

```bash
composer require --dev 'donatj/mock-webserver'
```

## Examples

### Basic Usage

The following example shows the most basic usage. If you do not define a path, the server will simply bounce a JSON body describing the request back to you.

```php
<?php

use donatj\MockWebServer\MockWebServer;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

$url = $server->getServerRoot() . '/endpoint?get=foobar';

echo "Requesting: $url\n\n";
echo file_get_contents($url);

```

Outputs:

```
Requesting: http://127.0.0.1:61874/endpoint?get=foobar

{
    "_GET": {
        "get": "foobar"
    },
    "_POST": [],
    "_FILES": [],
    "_COOKIE": [],
    "HEADERS": {
        "Host": "127.0.0.1:61874",
        "Connection": "close"
    },
    "METHOD": "GET",
    "INPUT": "",
    "PARSED_INPUT": [],
    "REQUEST_URI": "\/endpoint?get=foobar",
    "PARSED_REQUEST_URI": {
        "path": "\/endpoint",
        "query": "get=foobar"
    }
}
```

### Simple

```php
<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// We define the server's response to requests of the /definedPath endpoint
$url = $server->setResponseOfPath(
	'/definedPath',
	new Response(
		'This is our http body response',
		[ 'Cache-Control' => 'no-cache' ],
		200
	)
);

echo "Requesting: $url\n\n";

$content = file_get_contents($url);

// $http_response_header is a little known variable magically defined
// in the current scope by file_get_contents with the response headers
echo implode("\n", $http_response_header) . "\n\n";
echo $content . "\n";

```

Outputs:

```
Requesting: http://127.0.0.1:61874/definedPath

HTTP/1.1 200 OK
Host: 127.0.0.1:61874
Date: Tue, 31 Aug 2021 19:50:15 GMT
Connection: close
X-Powered-By: PHP/7.3.25
Cache-Control: no-cache
Content-type: text/html; charset=UTF-8

This is our http body response
```

### Change Default Response

```php
<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Responses\NotFoundResponse;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

// The default response is donatj\MockWebServer\Responses\DefaultResponse
// which returns an HTTP 200 and a descriptive JSON payload.
//
// Change the default response to donatj\MockWebServer\Responses\NotFoundResponse
// to get a standard 404.
//
// Any other response may be specified as default as well.
$server->setDefaultResponse(new NotFoundResponse);

$content = file_get_contents($server->getServerRoot() . '/PageDoesNotExist', false, stream_context_create([
	'http' => [ 'ignore_errors' => true ], // allow reading 404s
]));

// $http_response_header is a little known variable magically defined
// in the current scope by file_get_contents with the response headers
echo implode("\n", $http_response_header) . "\n\n";
echo $content . "\n";

```

Outputs:

```
HTTP/1.1 404 Not Found
Host: 127.0.0.1:61874
Date: Tue, 31 Aug 2021 19:50:15 GMT
Connection: close
X-Powered-By: PHP/7.3.25
Content-type: text/html; charset=UTF-8

VND.DonatStudios.MockWebServer: Resource '/PageDoesNotExist' not found!

```

### PHPUnit

```php
<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

class ExampleTest extends PHPUnit\Framework\TestCase {

	/** @var MockWebServer */
	protected static $server;

	public static function setUpBeforeClass() : void {
		self::$server = new MockWebServer;
		self::$server->start();
	}

	public function testGetParams() : void {
		$result  = file_get_contents(self::$server->getServerRoot() . '/autoEndpoint?foo=bar');
		$decoded = json_decode($result, true);
		$this->assertSame('bar', $decoded['_GET']['foo']);
	}

	public function testGetSetPath() : void {
		// $url = http://127.0.0.1:61874/definedEndPoint
		$url    = self::$server->setResponseOfPath('/definedEndPoint', new Response('foo bar content'));
		$result = file_get_contents($url);
		$this->assertSame('foo bar content', $result);
	}

	public static function tearDownAfterClass() : void {
		// stopping the web server during tear down allows us to reuse the port for later tests
		self::$server->stop();
	}

}

```

### Delayed Response Usage

By default responses will happen instantly. If you're looking to test timeouts, the DelayedResponse response wrapper may be useful.

```php
<?php

use donatj\MockWebServer\DelayedResponse;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

require __DIR__ . '/../vendor/autoload.php';

$server = new MockWebServer;
$server->start();

$response = new Response(
	'This is our http body response',
	[ 'Cache-Control' => 'no-cache' ],
	200
);

// Wrap the response in a DelayedResponse object, which will delay the response
$delayedResponse = new DelayedResponse(
	$response,
	100000 // sets a delay of 100000 microseconds (.1 seconds) before returning the response
);

$realtimeUrl = $server->setResponseOfPath('/realtime', $response);
$delayedUrl  = $server->setResponseOfPath('/delayed', $delayedResponse);

echo "Requesting: $realtimeUrl\n\n";

// This request will run as quickly as possible
$start = microtime(true);
file_get_contents($realtimeUrl);
echo "Realtime Request took: " . (microtime(true) - $start) . " seconds\n\n";

echo "Requesting: $delayedUrl\n\n";

// The request will take the delayed time + the time it takes to make and transfer the request
$start = microtime(true);
file_get_contents($delayedUrl);
echo "Delayed Request took: " . (microtime(true) - $start) . " seconds\n\n";

```

Outputs:

```
Requesting: http://127.0.0.1:61874/realtime

Realtime Request took: 0.015669107437134 seconds

Requesting: http://127.0.0.1:61874/delayed

Delayed Request took: 0.10729098320007 seconds

```

## Multiple Responses from the Same Endpoint

### Response Stack

If you need an ordered set of responses, that can be done using the ResponseStack.

```php
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

```

Outputs:

```
Requesting: http://127.0.0.1:61874/definedPath

Response One
Response Two
Past the end of the ResponseStack
```

### Response by Method

If you need to vary responses to a single endpoint by method, you can do that using the ResponseByMethod response object.

```php
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

```

Outputs:

```
GET request to http://127.0.0.1:61874/foo/bar:
This is our http GET response

POST request to http://127.0.0.1:61874/foo/bar:
This is our http POST response

```