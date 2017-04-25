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
$tmp                = getenv(MockWebServer::TMP_ENV);
$PARSED_REQUEST_URI = parse_url($_SERVER['REQUEST_URI']);

$HEADERS = getallheaders();
$INPUT   = file_get_contents("php://input");
parse_str($INPUT, $PARSED_INPUT);

$request = [
	'_GET'               => $_GET,
	'_POST'              => $_POST,
	'_FILES'             => $_FILES,
	'_COOKIE'            => $_COOKIE,
	'HEADERS'            => $HEADERS,
	'METHOD'             => $_SERVER['REQUEST_METHOD'],
	'INPUT'              => $INPUT,
	'PARSED_INPUT'       => $PARSED_INPUT,
	'REQUEST_URI'        => $_SERVER['REQUEST_URI'],
	'PARSED_REQUEST_URI' => $PARSED_REQUEST_URI,
];

file_put_contents($tmp . DIRECTORY_SEPARATOR . 'last.request', json_encode($request));

$path  = false;
$alias = 'alias.' . md5($PARSED_REQUEST_URI['path']);
if( file_exists($tmp . DIRECTORY_SEPARATOR . $alias) ) {
	if( $path = file_get_contents($tmp . DIRECTORY_SEPARATOR . $alias) ) {
		$path = $tmp . DIRECTORY_SEPARATOR . $path;
	}
} elseif( preg_match('%^/' . preg_quote(MockWebServer::VND) . '/([0-9a-fA-F]{32})$%', $_SERVER['REQUEST_URI'], $matches) ) {
	$path = $tmp . DIRECTORY_SEPARATOR . $matches[1];
}

if( $path !== false ) {
	if( is_readable($path) ) {
		$content  = file_get_contents($path);
		$response = json_decode($content, true);

		http_response_code($response[MockWebServer::RESPONSE_STATUS]);

		foreach( $response[MockWebServer::RESPONSE_HEADERS] as $key => $header ) {
			if( is_int($key) ) {
				header($header);
			} else {
				header("{$key}: {$header}");
			}
		}

		if( $response[MockWebServer::RESPONSE_BODY] !== false ) {
			echo $response[MockWebServer::RESPONSE_BODY];

			return;
		}
	} else {
		http_response_code(404);
		echo MockWebServer::VND . ": Resource '{$path}' not found!\n";

		return;
	}
} else {
	header('Content-Type: application/json');
}

echo json_encode($request, JSON_PRETTY_PRINT);