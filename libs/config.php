<?php

$dbSettings = [
	'database_type' => 'mysql',
	'server' => 'localhost',
	'database_name' => '',
	'username' => '',
	'password' => '',
	'charset' => 'utf8mb4',
];

$headers = [
	'Access-Control-Allow-Origin' => '*',
	'Access-Control-Allow-Headers' => [
		'Origin',
		'X-Requested-With',
		'Content-Type',
		'Accept',
		'Access-Control-Allow-Origin'
	]
];

$salt = '';

// Routing
    $routes = [
        // 'fakeEndpoint' => ['fileName', 'className'],
    ];
