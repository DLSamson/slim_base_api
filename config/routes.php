<?php

use Api\Core\Http\EchoController;
use Api\Controllers\UserController;

/* @var \Slim\App $app */
$app->get('/echo[/{value}]', [EchoController::class, 'echo']);
$app->get('/ping', [EchoController::class, 'ping']);

/* Testing example */
$app->get('/', function($req, $res, $arg) {
    $res->getBody()->write('Hello world!');
    return $res
        ->withStatus(200);
});

$app->post('/registration', [UserController::class, 'register'])->setName('register');