# Mock Web Server

[![Latest Stable Version](https://poser.pugx.org/donatj/mock-webserver/version)](https://packagist.org/packages/donatj/mock-webserver)
[![License](https://poser.pugx.org/donatj/mock-webserver/license)](https://packagist.org/packages/donatj/mock-webserver)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/donatj/mock-webserver/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/donatj/mock-webserver)
[![Build Status](https://travis-ci.org/donatj/mock-webserver.svg?branch=master)](https://travis-ci.org/donatj/mock-webserver)


Simple, easy to use Mock Web Server for PHP unit testing. Gets along simply with PHPUnit and other unit testing frameworks.

Unit testing HTTP requests can be difficult, especially in cases where injecting a request library is difficult or not ideal. This helps greatly simplify the process.

Mock Web Server creates a local Web Server you can make predefined requests against.


## Documentation

[See: docs/docs.md](docs/docs.md)

## Requirements

- **php**: >=5.4
- **ext-sockets**: *
- **ext-json**: *
- **ext-curl**: *
- **ralouphie/getallheaders**: ~2.0

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
Requesting: http://127.0.0.1:52142/endpoint?get=foobar

{
    "_GET": {
        "get": "foobar"
    },
    "_POST": [],
    "_FILES": [],
    "_COOKIE": [],
    "HEADERS": {
        "Host": "127.0.0.1:52142",
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

// We define the servers response to requests of the /definedPath endpoint
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
Requesting: http://127.0.0.1:52152/definedPath

HTTP/1.0 200 OK
Host: 127.0.0.1:52152
Date: Mon, 12 Mar 2018 18:11:33 +0000
Connection: close
X-Powered-By: PHP/7.1.7
Cache-Control: no-cache
Content-type: text/html; charset=UTF-8

This is our http body response
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
		// $url = http://127.0.0.1:8123/definedEndPoint
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
Requesting: http://127.0.0.1:52155/definedPath

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
Requesting: http://127.0.0.1:52159/definedPath

Response One
Response Two
Past the end of the ResponseStack
```
