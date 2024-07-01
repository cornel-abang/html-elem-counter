<?php

use Dotenv\Dotenv;

/**
 * Load: environment (.env) file
 * 
 * And access through global $_ENV
 */
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

return [
    'db' => [
        'host' => $_ENV['MYSQL_HOST'],
        'dbname' => $_ENV['MYSQL_DATABASE'],
        'user' => $_ENV['MYSQL_USER'],
        'password' => $_ENV['MYSQL_PASSWORD']
    ]
];