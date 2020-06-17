<?php

// Same settings database as in https://medoo.in/api/new
$dbSettings = [
	'database_type' => 'mysql',
	'server' => 'localhost',
	'database_name' => '',
	'username' => '',
	'password' => '',
	'charset' => 'utf8mb4',
];

// Default HTTP headers
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


// Password salt string
$salt = '';

// Routing
    $routes = [
        // 'fakeEndpoint' => ['fileName', 'className'],
    ];
