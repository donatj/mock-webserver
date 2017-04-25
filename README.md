# Mock Web Server

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/donatj/mock-webserver/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/donatj/mock-webserver/?branch=master)

Simple, easy to use Mock Web Server for PHP unit testing. Gets along simply with PHPUnit and other unit testing frameworks.

Unit testing HTTP requests can be difficult, especially in cases where injecting a request library is difficult or not ideal. This helps greatly simplify the process.

Mock Web Server creates a local Web Server you can make predefined requests against.


[See: docs/docs.md](docs/docs.md)



## Requirements

- PHP 5.4+

## Installing

```php
composer require "donatj/mock-webserver" --dev
```

Omitting the `--dev` will add this to `require` rather than `require-dev`

## Examples

### Simple

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

// $http_response_header is a little known variable magically defined
// in the current scope by file_get_contents with the response headers
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

### PHPUnit

```php
<?php

use donatj\MockWebServer\MockWebServer;

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
		$url    = self::$server->setResponseOfPath('/definedEndPoint', 'foo bar content');
		$result = file_get_contents($url);
		$this->assertSame('foo bar content', $result);
	}

	static function tearDownAfterClass() {
		// stopping the webs erver during tear down allows us to reuse the port for later tests
		self::$server->stop();
	}

}

```