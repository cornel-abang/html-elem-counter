<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables from .env file if exists
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}
