<?php

use Api\Controllers\EchoController;
use Api\Controllers\AccountController;
use Slim\Routing\RouteCollectorProxy;
use Api\Core\Services\Authorization;

/* @var \Slim\App $app */
$app->get('/echo[/{value}]', [EchoController::class, 'echo']);
$app->get('/ping', [EchoController::class, 'ping']);

/* Testing example */
$app->get('/', function ($req, $res) {
    phpinfo();
    return $res->withStatus(200);
});

/* Requrie authoriaztion */
$app->group('', function (RouteCollectorProxy $group) {

    $group->post('/registration', [AccountController::class, 'register'])->setName('register');

    $group->put('/accounts[/{accountId}]', [AccountController::class, 'update'])->setName('updateUser');

})->add([Authorization::class, 'AuthStrict']);


/* Requrie allow null authorization */
$app->group('', function (RouteCollectorProxy $group) {

    $group->get('/accounts/search', [AccountController::class, 'searchParams'])->setName('searchUserWithParams');
    $group->get('/accounts[/{accountId}]', [AccountController::class, 'searchId'])->setName('searchUserWithId');

    //$group->get('/animals/{animalId}', [])->setName('');
    //$group->get('/animals/search', [])->setName('');
    //$group->get('/animals/types/{typeId}', [])->setName('');

    //$group->get('/locations/{pointId}', [])->setName('');

    //$group->get('/animals/{animalId}/locations', [])->setName('');

})->add([Authorization::class, 'AuthAllowNull']);