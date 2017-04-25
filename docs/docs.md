# Documentation

## Class: MockWebServer \[ `\donatj\MockWebServer` \]

### Method: `MockWebServer->__construct([ $port = 8123 [, $host = "127.0.0.1"]])`

TestWebServer constructor.

#### Parameters:

- ***int*** `$port` - Network port to run on
- ***string*** `$host` - Listening hostname

---

### Method: `MockWebServer->start()`

Start the Web Server on the selected port and host

---

### Method: `MockWebServer->isRunning()`

Is the Web Server currently running?

#### Returns:

- ***bool***

---

### Method: `MockWebServer->stop()`

Stop the Web Server

---

### Method: `MockWebServer->getServerRoot()`

Get the HTTP root of the webserver  
 e.g.: http://127.0.0.1:8123

#### Returns:

- ***string***

---

### Method: `MockWebServer->getUrlOfResponse($body [, $headers = array() [, $status = 200]])`

Get a URL providing the specified response.

#### Parameters:

- ***string*** `$body`
- ***array*** `$headers`
- ***int*** `$status`

#### Returns:

- ***string*** - URL where response can be found

---

### Method: `MockWebServer->setResponseOfPath($path, $body [, $headers = array() [, $status = 200]])`

Set a specified path to provide a specific response

#### Parameters:

- ***string*** `$path`
- ***string*** `$body`
- ***array*** `$headers`
- ***int*** `$status`

#### Returns:

- ***string***

---

### Method: `MockWebServer->getLastRequest()`

Get the previous requests associated request data.

#### Returns:

- ***array*** | ***null***

---

### Method: `MockWebServer->getHost()`

Get the host of the server.

#### Returns:

- ***string***

---

### Method: `MockWebServer->getPort()`

Get the port the network server is to be ran on.

#### Returns:

- ***int***