<?php

use Api\Core\Http\EchoController;
use Api\Controllers\AccountsController;

/* @var \Slim\App $app */
$app->get('/echo[/{value}]', [EchoController::class, 'echo']);
$app->get('/ping', [EchoController::class, 'ping']);

/* Testing example */
$app->get('/', function($req, $res, $arg) {
    $res->getBody()->write('Hello world!');
    return $res
        ->withStatus(200);
});

$app->post('/registration', [AccountsController::class, 'register'])->setName('register');
$app->get('/accounts/search', [AccountsController::class, 'searchParams'])->setName('searchUserWithParams');
$app->get('/accounts[/{accountId}]', [AccountsController::class, 'searchId'])->setName('searchUserWithId');
//$app->put('/accounts[/{accountId}]', [AccountsController::class, 'update'])->setName('updateUser');