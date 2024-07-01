<?php
require_once 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use ElementCounter\Service\Request;
use ElementCounter\Controller\RequestController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new RequestController(new Request());
    $controller->handleRequest();
} else {
    require 'app/View/Home.php';
}