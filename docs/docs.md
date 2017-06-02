# Documentation

## Class: \donatj\MockWebServer\MockWebServer

```php
<?php
namespace donatj\MockWebServer;

class MockWebServer {
	const VND = 'VND.DonatStudios.MockWebServer';
	const RESPONSE_BODY = 'body';
	const RESPONSE_STATUS = 'status';
	const RESPONSE_HEADERS = 'headers';
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
function getUrlOfResponse($body [, $headers = array() [, $status = 200]])
```

Get a URL providing the specified response.

#### Parameters:

- ***string*** `$body`
- ***array*** `$headers`
- ***int*** `$status`

#### Returns:

- ***string*** - URL where response can be found

---

### Method: MockWebServer->setResponseOfPath

```php
function setResponseOfPath($path, $body [, $headers = array() [, $status = 200]])
```

Set a specified path to provide a specific response

#### Parameters:

- ***string*** `$path`
- ***string*** `$body`
- ***array*** `$headers`
- ***int*** `$status`

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