<?php
error_reporting(E_ALL);
require '../vendor/autoload.php';

if(!defined('ROOT_DIR'))
    define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR.'/config/bootstrap.php';
require_once ROOT_DIR.'/config/routes.php';

/* @var \Slim\App $app */
$app->run();