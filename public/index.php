<?php

error_reporting(E_ERROR);
require_once '../vendor/autoload.php';
require_once dirname(__DIR__) .'/config/bootstrap.php';
require_once ROOT_DIR.'/config/routes.php';

/* @var \Slim\App $app */
$app->run();