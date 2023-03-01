<?php

use Api\Controllers\EchoController;
use Api\Controllers\Pages\IndexController;

/* @var \Slim\App $app */
$app->get('/echo[/{value}]', [EchoController::class, 'echo']);
$app->get('/ping', [EchoController::class, 'ping']);

/* Testing example */
$app->get('/', function($req, $res, $arg) {
    $res->getBody()->write('Hello world!');
    return $res
        ->withStatus(200);
});