<?php

use Api\Controllers\EchoController;
use Api\Controllers\AccountController;

/* @var \Slim\App $app */
$app->get('/echo[/{value}]', [EchoController::class, 'echo']);
$app->get('/ping', [EchoController::class, 'ping']);

/* Testing example */
$app->get('/', function ($req, $res, $arg) {
    phpinfo();

    return $res->withStatus(200);
});

$app->post('/registration', [AccountController::class, 'register'])->setName('register');
$app->get('/accounts/search', [AccountController::class, 'searchParams'])->setName('searchUserWithParams');
$app->get('/accounts[/{accountId}]', [AccountController::class, 'searchId'])->setName('searchUserWithId');
$app->put('/accounts[/{accountId}]', [AccountController::class, 'update'])->setName('updateUser');

