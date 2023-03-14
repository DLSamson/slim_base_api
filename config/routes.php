<?php

use Api\Controllers\AnimalController;
use Api\Controllers\AccountController;
use Api\Controllers\LocationController;
use Slim\Routing\RouteCollectorProxy;
use Api\Core\Services\Authorization;
use Api\Controllers\TypeController;

/* @var \Slim\App $app */

/* Requrie authoriaztion */
$app->group('', function (RouteCollectorProxy $group) {
    $group->put('/accounts/{accountId}', [AccountController::class, 'update'])
        ->setName('user.update');
    $group->delete('/accounts/{accountId}', [AccountController::class, 'delete'])
        ->setName('user.delete');

    $group->post('/locations', [LocationController::class, 'create'])
        ->setName('location.create');
    $group->put('/locations/{pointId}', [LocationController::class, 'update'])
        ->setName('location.update');
    $group->delete('/locations/{pointId}', [LocationController::class, 'delete'])
        ->setName('location.delete');

    $group->post('/animals/types', [TypeController::class, 'create'])
        ->setName('type.create');
    $group->put('/animals/types/{typeId}', [TypeController::class, 'update'])
        ->setName('type.update');
    $group->delete('/animals/types/{typeId}', [TypeController::class, 'delete'])
        ->setName('type.delete');

    $group->post('/animals', [AnimalController::class, 'create'])
        ->setName('animal.create');
    $group->put('/animals/{animalId}', [AnimalController::class, 'update'])
        ->setName('animal.update');
    $group->delete('/animals/{animalId}', [AnimalController::class, 'delete'])
        ->setName('animal.delete');

    $group->post('/animals/{animalId}/types/{typeId}', [AnimalController::class, 'addType'])
        ->setName('animal.type.add');
    $group->put('/animals/{animalId}/types', [AnimalController::class, 'updateType'])
        ->setName('animal.type.update');
    $group->delete('/animals/{animalId}/types/{typeId}', [AnimalController::class, 'deleteType'])
        ->setName('animal.type.delete');

    $group->post('/animals/{animalId}/locations/{pointId}', [AnimalController::class, 'locationAdd'])
        ->setName('animal.locations.create');
    $group->put('/animals/{animalId}/locations', [AnimalController::class, 'locationUpdate'])
        ->setName('animal.locations.update');
    $group->delete('/animals/{animalId}/locations/{visitedPointId}', [AnimalController::class, 'locationDelete'])
        ->setName('animal.locations.delete');
})
    ->add([Authorization::class, 'AuthStrict']);


/* Allow null authorization */
$app->group('', function (RouteCollectorProxy $group) {
    $group->get('/accounts/search', [AccountController::class, 'searchParams'])
        ->setName('user.searchParams');
    $group->get('/accounts[/{accountId}]', [AccountController::class, 'searchId'])
        ->setName('user.searchId');

    $group->get('/animals/search', [AnimalController::class, 'searchParams'])
        ->setName('animal.searchParams');
    $group->get('/animals/{animalId}', [AnimalController::class, 'searchId'])
        ->setName('animal.searchId');

    $group->get('/animals/types/{typeId}', [TypeController::class, 'search'])
        ->setName('animal.type');

    $group->get('/animals/{animalId}/locations', [AnimalController::class, 'locationsSearch'])
        ->setName('animal.locations');

    $group->get('/locations/{pointId}', [LocationController::class, 'search'])
        ->setName('location.searchId');
})
    ->add([Authorization::class, 'AuthAllowNull']);

/* Not allowed for authorized users */
$app->group('', function (RouteCollectorProxy $group) {
    $group->post('/registration', [AccountController::class, 'register'])->setName('user.create');
})
    ->add([Authorization::class, 'AuthNotAllowed']);