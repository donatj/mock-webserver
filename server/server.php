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

if( preg_match('%^/' . preg_quote(MockWebServer::VND) . '/([0-9a-fA-F]{32})$%', $_SERVER['REQUEST_URI'], $matches) ) {
	$path = MockWebServer::getTmpDir() . DIRECTORY_SEPARATOR . $matches[1];
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

$headers = getallheaders();

$INPUT = file_get_contents("php://input");
parse_str($INPUT, $PARSED_INPUT);

echo json_encode([
	'_GET'               => $_GET,
	'_POST'              => $_POST,
	'_FILES'             => $_FILES,
	'_COOKIE'            => $_COOKIE,
	'HEADERS'            => $headers,
	'METHOD'             => $_SERVER['REQUEST_METHOD'],
	'INPUT'              => $INPUT,
	'PARSED_INPUT'       => $PARSED_INPUT,
	'REQUEST_URI'        => $_SERVER['REQUEST_URI'],
	'PARSED_REQUEST_URI' => parse_url($_SERVER['REQUEST_URI']),
], JSON_PRETTY_PRINT);