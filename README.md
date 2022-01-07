# Mock Web Server

[![Latest Stable Version](https://poser.pugx.org/donatj/mock-webserver/version)](https://packagist.org/packages/donatj/mock-webserver)
[![License](https://poser.pugx.org/donatj/mock-webserver/license)](https://packagist.org/packages/donatj/mock-webserver)
[![CI](https://github.com/donatj/mock-webserver/workflows/CI/badge.svg?)](https://github.com/donatj/mock-webserver/actions?query=workflow%3ACI)
[![Build Status](https://travis-ci.org/donatj/mock-webserver.svg?branch=master)](https://travis-ci.org/donatj/mock-webserver)


Simple, easy to use Mock Web Server for PHP unit testing. Gets along simply with PHPUnit and other unit testing frameworks.

Unit testing HTTP requests can be difficult, especially in cases where injecting a request library is difficult or not ideal. This helps greatly simplify the process.

Mock Web Server creates a local Web Server you can make predefined requests against.


## Limitations

Unfortunately, Mock Web Server does not currently support Windows natively. It does work in [Windows Subsystem for Linux](https://docs.microsoft.com/en-us/windows/wsl/install-win10), and that would be my recommended route for Windows users currently.

There has been work [started to implement this](https://github.com/donatj/mock-webserver/pull/15) but I need help finishing it. If anyone is interested in helping, comment on [the pull request](https://github.com/donatj/mock-webserver/pull/15).

## Documentation

[See: docs/docs.md](docs/docs.md)

## Requirements

- **php**: >=5.4
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
Requesting: http://127.0.0.1:61355/endpoint?get=foobar

{
    "_GET": {
        "get": "foobar"
    },
    "_POST": [],
    "_FILES": [],
    "_COOKIE": [],
    "HEADERS": {
        "Host": "127.0.0.1:61355",
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
Requesting: http://127.0.0.1:61355/definedPath

HTTP/1.0 200 OK
Host: 127.0.0.1:61355
Connection: close
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
HTTP/1.0 404 Not Found
Host: 127.0.0.1:61355
Connection: close
Content-type: text/html; charset=UTF-8

VND.DonatStudios.MockWebServer: Resource '/PageDoesNotExist' not found!

```

### PHPUnit

```php
<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

class ExampleTest extends PHPUnit_Framework_TestCase {

	/** @var MockWebServer */
	protected static $server;

	public static function setUpBeforeClass() {
		self::$server = new MockWebServer;
		self::$server->start();
	}

	public function testGetParams() {
		$result  = file_get_contents(self::$server->getServerRoot() . '/autoEndpoint?foo=bar');
		$decoded = json_decode($result, true);
		$this->assertSame('bar', $decoded['_GET']['foo']);
	}

	public function testGetSetPath() {
		// $url = http://127.0.0.1:61355/definedEndPoint
		$url    = self::$server->setResponseOfPath('/definedEndPoint', new Response('foo bar content'));
		$result = file_get_contents($url);
		$this->assertSame('foo bar content', $result);
	}

	static function tearDownAfterClass() {
		// stopping the web server during tear down allows us to reuse the port for later tests
		self::$server->stop();
	}

}
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
Requesting: http://127.0.0.1:61355/definedPath

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
GET request to http://127.0.0.1:61355/foo/bar:
This is our http GET response

POST request to http://127.0.0.1:61355/foo/bar:
This is our http POST response

```