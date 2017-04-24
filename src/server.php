<?php

header('Content-Type: application/json');

$INPUT = file_get_contents("php://input");
parse_str($INPUT, $PARSED_INPUT);

echo json_encode([
	'_GET'               => $_GET,
	'_POST'              => $_POST,
	'_FILES'             => $_FILES,
	'METHOD'             => $_SERVER['REQUEST_METHOD'],
	'INPUT'              => $INPUT,
	'PARSED_INPUT'       => $PARSED_INPUT,
	'REQUEST_URI'        => $_SERVER['REQUEST_URI'],
	'PARSED_REQUEST_URI' => parse_url($_SERVER['REQUEST_URI']),
], JSON_PRETTY_PRINT);