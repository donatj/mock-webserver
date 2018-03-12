# Documentation

## Class: \donatj\MockWebServer\MockWebServer

```php
<?php
namespace donatj\MockWebServer;

class MockWebServer {
	const VND = 'VND.DonatStudios.MockWebServer';
	const LAST_REQUEST_FILE = 'last.request';
	const REQUEST_COUNT_FILE = 'count.request';
	const TMP_ENV = 'MOCK_WEB_SERVER_TMP';
}
```

### Method: MockWebServer->__construct

```php
function __construct($port [, $host = '127.0.0.1'])
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
function getUrlOfResponse($response)
```

Get a URL providing the specified response.

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface*** `$response`

#### Returns:

- ***string*** - URL where response can be found

---

### Method: MockWebServer->setResponseOfPath

```php
function setResponseOfPath($path, $response)
```

Set a specified path to provide a specific response

#### Parameters:

- ***string*** `$path`
- ***\donatj\MockWebServer\ResponseInterface*** `$response`

#### Returns:

- ***string***

---

### Method: MockWebServer->getLastRequest

```php
function getLastRequest()
```

Get the previous requests associated request data.

#### Returns:

- ***array*** | ***null***

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

- ***array*** | ***null***

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

```php
<?php
namespace donatj\MockWebServer;

class Response {
	const RESPONSE_BODY = 'body';
	const RESPONSE_STATUS = 'status';
	const RESPONSE_HEADERS = 'headers';
}
```

### Method: Response->__construct

```php
function __construct($body [, $headers = array() [, $status = 200]])
```

Response constructor.

#### Parameters:

- ***string*** `$body`
- ***array*** `$headers`
- ***int*** `$status`

## Class: \donatj\MockWebServer\ResponseStack

```php
<?php
namespace donatj\MockWebServer;

class ResponseStack {
	const RESPONSE_BODY = 'body';
	const RESPONSE_STATUS = 'status';
	const RESPONSE_HEADERS = 'headers';
}
```

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
function setPastEndResponse($pastEndResponse)
```

#### Parameters:

- ***\donatj\MockWebServer\ResponseInterface*** `$pastEndResponse`