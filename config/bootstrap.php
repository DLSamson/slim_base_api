<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;

/* Setting up project ROOT_DIR */
if(!defined('ROOT_DIR'))
    define('ROOT_DIR', dirname(__DIR__));

/* Check if all folders are created */
if(!is_dir(ROOT_DIR.'/cache'))
    mkdir(ROOT_DIR.'/cache');

if(!is_dir(ROOT_DIR.'/cache/templates'))
    mkdir(ROOT_DIR.'/cache/templates');

if(!is_dir(ROOT_DIR.'/log'))
    mkdir(ROOT_DIR.'/log');

/* Setting up DI Container */
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->addDefinitions(require 'container.php');
$container = $containerBuilder->build();

/* Require .env file */
$dotenv = Dotenv::createImmutable(ROOT_DIR);
try {
    $dotenv->load();
    $dotenv
        ->required([

        ])
        ->notEmpty();
} catch (Throwable $exception) {
    $logger = $container->get(LoggerInterface::class);
    $logger->error($exception->getMessage());
    die();
}

/* Setting up database */
$capsule = new Capsule();
$capsule->addConnection([

]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

/* Creating Slim instance */
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addErrorMiddleware(
    true,
    true,
    true
);