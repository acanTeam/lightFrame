<?php

return array(

	// Typical Database configuration
	'normal' => array(
		'dsn' => '',
		'hostname' => 'localhost',
		'username' => 'root',
		'password' => '',
		'database' => 'test',
		'dbdriver' => 'mysqli'
	),

	'error' => array(
		'dsn' => '',
		'hostname' => 'localhost',
		'username' => 'not_travis',
		'password' => 'wrong password',
		'database' => 'not_ci_test',
		'dbdriver' => 'mysqli',
    ),

	// Database configuration with failover
	'failover' => array(
		'dsn' => '',
		'hostname' => 'localhost',
		'username' => 'not_travis',
		'password' => 'wrong password',
		'database' => 'not_ci_test',
		'dbdriver' => 'mysqli',
		'failover' => array(
			array(
				'dsn' => '',
				'hostname' => 'localhost',
				'username' => 'root',
				'password' => '',
				'database' => 'test',
				'dbdriver' => 'mysqli',
			)
		)
	)
);
