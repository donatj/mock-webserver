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

$INPUT = file_get_contents("php://input");

$HEADERS = getallheaders();

$tmp = getenv(MockWebServer::TMP_ENV);

$x = new \donatj\MockWebServer\InternalServer(
	$tmp, $_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $HEADERS, $INPUT
);

$x();
