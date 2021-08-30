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
function __construct([ $port = 0 [, $host = '127.0.0.1']])
```

TestWebServer constructor.

#### Parameters:

- ***int*** `$port` - Network port to run on
- ***string*** `$host` - Listening hostname

---

### Method: MockWebServer->start

```php
function start()
```

Start the Web Server on the selected port and host

---

### Method: MockWebServer->isRunning

```php
function isRunning()
```

Is the Web Server currently running?

#### Returns:

- ***bool***

---

### Method: MockWebServer->stop

```php
function stop()
```

Stop the Web Server

---

### Method: MockWebServer->getServerRoot

```php
function getServerRoot()
```

Get the HTTP root of the webserver  
 e.g.: http://127.0.0.1:8123

#### Returns:

- ***string***

---

### Method: MockWebServer->getUrlOfResponse

```php
function getUrlOfResponse(\donatj\MockWebServer\ResponseInterface $response)
```

Get a URL providing the specified response.

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface*** `$response`

#### Returns:

- ***string*** - URL where response can be found

---

### Method: MockWebServer->setResponseOfPath

```php
function setResponseOfPath($path, \donatj\MockWebServer\ResponseInterface $response)
```

Set a specified path to provide a specific response

#### Parameters:

- ***string*** `$path`
- ***\donatj\MockWebServer\ResponseInterface*** `$response`

#### Returns:

- ***string***

---

### Method: MockWebServer->setDefaultResponse

```php
function setDefaultResponse(\donatj\MockWebServer\ResponseInterface $response)
```

Override the default server response, e.g. Fallback or 404

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface*** `$response`

#### Returns:

- ***void***

---

### Method: MockWebServer->getLastRequest

```php
function getLastRequest()
```

Get the previous requests associated request data.

#### Returns:

- ***\donatj\MockWebServer\RequestInfo*** | ***null***

---

### Method: MockWebServer->getRequestByOffset

```php
function getRequestByOffset($offset)
```

Get request by offset  
  
If offset is non-negative, the request will be the index from the start of the server.  
If offset is negative, the request will be that from the end of the requests.

#### Parameters:

- ***int*** `$offset`

#### Returns:

- ***\donatj\MockWebServer\RequestInfo*** | ***null***

---

### Method: MockWebServer->getHost

```php
function getHost()
```

Get the host of the server.

#### Returns:

- ***string***

---

### Method: MockWebServer->getPort

```php
function getPort()
```

Get the port the network server is to be ran on.

#### Returns:

- ***int***

## Class: \donatj\MockWebServer\Response

### Method: Response->__construct

```php
function __construct($body [, array $headers = [] [, $status = 200]])
```

Response constructor.

#### Parameters:

- ***string*** `$body`
- ***array*** `$headers`
- ***int*** `$status`

## Class: \donatj\MockWebServer\ResponseStack

### Method: ResponseStack->__construct

```php
function __construct()
```

ResponseStack constructor.  
  
Accepts a variable number of RequestInterface objects

---

### Method: ResponseStack->getPastEndResponse

```php
function getPastEndResponse()
```

#### Returns:

- ***\donatj\MockWebServer\ResponseInterface***

---

### Method: ResponseStack->setPastEndResponse

```php
function setPastEndResponse(\donatj\MockWebServer\ResponseInterface $pastEndResponse)
```

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface*** `$pastEndResponse`

## Class: \donatj\MockWebServer\ResponseByMethod

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
function __construct([ array $responses = [] [, \donatj\MockWebServer\ResponseInterface $defaultResponse = null]])
```

MethodResponse constructor.

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface[]*** `$responses` - An array of responses keyed by their method.
- ***\donatj\MockWebServer\ResponseInterface*** | ***null*** `$defaultResponse` - The fallthrough response to return if a response for a given
method is not found. If this is not defined the server will return an HTTP 501 error.

---

### Method: ResponseByMethod->setMethodResponse

```php
function setMethodResponse($method, \donatj\MockWebServer\ResponseInterface $response)
```

Set the Response for the Given Method

#### Parameters:

- ***string*** `$method`
- ***\donatj\MockWebServer\ResponseInterface*** `$response`