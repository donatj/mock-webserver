# Documentation

## Class: \donatj\MockWebServer\MockWebServer

```php
<?php
namespace donatj\MockWebServer;

class MockWebServer {
	public const VND = 'VND.DonatStudios.MockWebServer';
	public const LAST_REQUEST_FILE = 'last.request';
	public const REQUEST_COUNT_FILE = 'count.request';
	public const TMP_ENV = 'MOCK_WEB_SERVER_TMP';
}
```

### Method: MockWebServer->__construct

```php
function __construct([ int $port = 0 [, string $host = '127.0.0.1']])
```

TestWebServer constructor.

#### Parameters:

- ***int*** `$port` - Network port to run on
- ***string*** `$host` - Listening hostname

---

### Method: MockWebServer->start

```php
function start() : void
```

Start the Web Server on the selected port and host

---

### Method: MockWebServer->isRunning

```php
function isRunning() : bool
```

Is the Web Server currently running?

---

### Method: MockWebServer->stop

```php
function stop() : void
```

Stop the Web Server

---

### Method: MockWebServer->getServerRoot

```php
function getServerRoot() : string
```

Get the HTTP root of the webserver  
 e.g.: http://127.0.0.1:8123

---

### Method: MockWebServer->getUrlOfResponse

```php
function getUrlOfResponse(\donatj\MockWebServer\ResponseInterface $response) : string
```

Get a URL providing the specified response.

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface*** `$response`

#### Returns:

- ***string*** - URL where response can be found

---

### Method: MockWebServer->setResponseOfPath

```php
function setResponseOfPath(string $path, \donatj\MockWebServer\ResponseInterface $response) : string
```

Set a specified path to provide a specific response

---

### Method: MockWebServer->setDefaultResponse

```php
function setDefaultResponse(\donatj\MockWebServer\ResponseInterface $response) : void
```

Override the default server response, e.g. Fallback or 404

---

### Method: MockWebServer->getLastRequest

```php
function getLastRequest() : ?\donatj\MockWebServer\RequestInfo
```

Get the previous requests associated request data.

---

### Method: MockWebServer->getRequestByOffset

```php
function getRequestByOffset(int $offset) : ?\donatj\MockWebServer\RequestInfo
```

Get request by offset  
  
If offset is non-negative, the request will be the index from the start of the server.  
If offset is negative, the request will be that from the end of the requests.

---

### Method: MockWebServer->getHost

```php
function getHost() : string
```

Get the host of the server.

---

### Method: MockWebServer->getPort

```php
function getPort() : int
```

Get the port the network server is to be ran on.

## Class: \donatj\MockWebServer\Response

### Method: Response->__construct

```php
function __construct(string $body [, array $headers = [] [, int $status = 200]])
```

Response constructor.

## Class: \donatj\MockWebServer\ResponseStack

ResponseStack is used to store multiple responses for a request issued by the server in order.

When the stack is empty, the server will return a customizable response defaulting to a 404.

### Method: ResponseStack->__construct

```php
function __construct()
```

ResponseStack constructor.  
  
Accepts a variable number of RequestInterface objects

---

### Method: ResponseStack->getPastEndResponse

```php
function getPastEndResponse() : \donatj\MockWebServer\ResponseInterface
```

Gets the response returned when the stack is exhausted.

---

### Method: ResponseStack->setPastEndResponse

```php
function setPastEndResponse(\donatj\MockWebServer\ResponseInterface $pastEndResponse)
```

Set the response to return when the stack is exhausted.

## Class: \donatj\MockWebServer\ResponseByMethod

ResponseByMethod is used to vary the response to a request by the called HTTP Method.

```php
<?php
namespace donatj\MockWebServer;

class ResponseByMethod {
	public const METHOD_GET = 'GET';
	public const METHOD_POST = 'POST';
	public const METHOD_PUT = 'PUT';
	public const METHOD_PATCH = 'PATCH';
	public const METHOD_DELETE = 'DELETE';
	public const METHOD_HEAD = 'HEAD';
	public const METHOD_OPTIONS = 'OPTIONS';
	public const METHOD_TRACE = 'TRACE';
}
```

### Method: ResponseByMethod->__construct

```php
function __construct([ array $responses = [] [, ?\donatj\MockWebServer\ResponseInterface $defaultResponse = null]])
```

MethodResponse constructor.

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface[]*** `$responses` - A map of responses keyed by their method.
- ***\donatj\MockWebServer\ResponseInterface*** | ***null*** `$defaultResponse` - The fallthrough response to return if a response for a given
method is not found. If this is not defined the server will return an HTTP 501 error.

---

### Method: ResponseByMethod->setMethodResponse

```php
function setMethodResponse(string $method, \donatj\MockWebServer\ResponseInterface $response) : void
```

Set the Response for the Given Method

## Class: \donatj\MockWebServer\DelayedResponse

DelayedResponse wraps a response, causing it when called to be delayed by a specified number of microseconds.

This is useful for simulating slow responses and testing timeouts.

### Method: DelayedResponse->__construct

```php
function __construct(\donatj\MockWebServer\ResponseInterface $response, $delay)
```

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface*** `$response`
- ***int*** `$delay` - Microseconds to delay the response

## Built-In Responses

### Class: \donatj\MockWebServer\Responses\DefaultResponse

The Built-In Default Response.

Results in an HTTP 200 with a JSON encoded version of the incoming Request

### Class: \donatj\MockWebServer\Responses\NotFoundResponse

Basic Built-In 404 Response