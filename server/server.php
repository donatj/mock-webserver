<?php

use donatj\MockWebServer\MockWebServer;

$files = [
	__DIR__ . '/../vendor/autoload.php',
	__DIR__ . '/../../../autoload.php',
];
foreach( $files as $file ) {
	if( file_exists($file) ) {
		require_once $file;
		break;
	}
}

$INPUT   = file_get_contents("php://input");
if( $INPUT === false ) {
	throw new RuntimeException('Failed to read php://input');
}

$HEADERS = getallheaders();

$tmp = getenv(MockWebServer::TMP_ENV);
if( $tmp === false || $tmp === '' ) {
	throw new RuntimeException('Environment variable ' . MockWebServer::TMP_ENV . ' is not set');
}

$r      = new \donatj\MockWebServer\RequestInfo($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $HEADERS, $INPUT);
$server = new \donatj\MockWebServer\InternalServer($tmp, $r);

$server();
