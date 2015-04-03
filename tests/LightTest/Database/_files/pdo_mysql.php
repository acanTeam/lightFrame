<?php

return array(

	// Typical Database configuration
	'normal' => array(
		'dsn' => 'mysql:host=localhost;dbname=test',
		'hostname' => 'localhost',
		'username' => 'travis',
		'password' => '',
		'database' => 'ci_test',
		'dbdriver' => 'pdo',
		'subdriver' => 'mysql'
	),

	'error' => array(
		'dsn' => 'mysql:host=localhost;dbname=test',
		'hostname' => 'localhost',
		'username' => 'noexist',
		'password' => 'wrongpwd',
		'database' => 'test',
		'dbdriver' => 'pdo',
		'subdriver' => 'mysql'
	),

	// Database configuration with failover
	'failover' => array(
		'dsn' => '',
		'hostname' => 'localhost',
		'username' => 'not_travis',
		'password' => 'wrong password',
		'database' => 'not_ci_test',
		'dbdriver' => 'pdo',
		'subdriver' => 'mysql',
		'failover' => array(
			array(
				'dsn' => 'mysql:host=localhost;dbname=test',
				'hostname' => 'localhost',
				'username' => 'root',
				'password' => '',
				'database' => 'test',
				'dbdriver' => 'pdo',
				'subdriver' => 'mysql'
			)
		)
	)
);
