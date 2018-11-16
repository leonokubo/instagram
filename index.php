<?php

require_once realpath(dirname(__DIR__) . '/html/vendor/autoload.php');

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Instantiate the app
$settings = require realpath(dirname(__DIR__) . '/html/app/settings.php');
$app = new \Slim\App($settings);

// Set up dependencies
require realpath(dirname(__DIR__) . '/html/app/dependencies.php');

// Register middleware
require realpath(dirname(__DIR__) . '/html/app/middleware.php');

// Register routes
require realpath(dirname(__DIR__) . '/html/app/routes.php');
// Run app
$app->run();
