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
    $dotenv->safeLoad();
    $dotenv
        ->required([
            'DB_HOST',
            'DB_NAME',
            'DB_USERNAME',
            'DB_PASSWORD'
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
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => $_ENV['DB_PREFIX'] ?: '',
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