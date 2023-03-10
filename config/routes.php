<?php

use Api\Controllers\AnimalController;
use Api\Controllers\EchoController;
use Api\Controllers\AccountController;
use Api\Controllers\LocationController;
use Slim\Routing\RouteCollectorProxy;
use Api\Core\Services\Authorization;
use Api\Controllers\TypeController;

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
    $group->put('/accounts[/{accountId}]', [AccountController::class, 'update'])->setName('updateUser');
})
    ->add([Authorization::class, 'AuthStrict']);


/* Requrie allow null authorization */
$app->group('', function (RouteCollectorProxy $group) {
    $group->get('/accounts/search', [AccountController::class, 'searchParams'])->setName('user.searchParams');
    $group->get('/accounts[/{accountId}]', [AccountController::class, 'searchId'])->setName('user.searchId');

    $group->get('/animals/search', [AnimalController::class, 'searchParams'])->setName('animal.searchParams');
    $group->get('/animals/{animalId}', [AnimalController::class, 'searchId'])->setName('animal.searchId');
    $group->get('/animals/types/{typeId}', [TypeController::class, 'search'])->setName('animal.type');
    $group->get('/animals/{animalId}/locations', [AnimalController::class, 'locations'])->setName('animal.locations');

    $group->get('/locations/{pointId}', [LocationController::class, 'search'])->setName('location.searchId');
})
    ->add([Authorization::class, 'AuthAllowNull']);

/* Not allowed for authorized users */
$app->group('', function (RouteCollectorProxy $group) {
    $group->post('/registration', [AccountController::class, 'register'])->setName('user.create');
})
    ->add([Authorization::class, 'AuthNowAllowed']);