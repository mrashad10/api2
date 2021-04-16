<?php

// Same settings database as in https://medoo.in/api/new
$dbSettings = [
	'database_type' => 'sqlite',
	'database_file' => 'database.db',
];

// General system variables
	$headers = [
		'vary' 								=> 'Origin, Accept-Encoding',
		'access-control-allow-credentials' 	=> 'true',
		'cache-control' 					=> 'no-cache',
		'pragma' 							=> 'no-cache',
		'expires' 							=> "-1",
		// 'x-content-type-options' 		=>'nosniff',
		'Access-Control-Allow-Origin'		=> '*',
		'Access-Control-Allow-Headers' 		=> [
			'Origin',
			'X-Requested-With',
			'Content-Type',
			'Accept',
			'Authorization',
			'Access-Control-Allow-Origin',
			'Accept-Language'
		]
	];

$systemVariables = (object)[
    'defaultLanguage'      => 'en', // iso-code
    'smtpServer'           => '',
    'smtpTlsPort'          => '',
    'smtpUser'             => '',
    'smtpPassword'         => '',
    'emailFromEmail'       => '',
    'emailFromName'        => 'API2',
    'tokenLiveTime'        => 1, // In mints
	'salt'				   => sha1('7tvhbi;ou9u'), // Password Salt //FIXME: Must change in production
	'secretKey'			   => sha1('ghr56fgp98g-0pei') // JWT key //FIXME: Must change in production
];

// Routing
    $routes = [
        // 'fakeEndpoint' => ['fileName', 'className'],
    ];
