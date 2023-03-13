<?php

error_reporting(E_ERROR);
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;;

Capsule::schema()->dropIfExists('animals_locations');
Capsule::schema()->dropIfExists('animals_types');
Capsule::schema()->dropIfExists('animals');
Capsule::schema()->dropIfExists('users');
Capsule::schema()->dropIfExists('locations');
Capsule::schema()->dropIfExists('types');

Capsule::schema()->create('users', function (Blueprint $table) {
    $table->id();

    $table->string('firstName');
    $table->string('lastName');
    $table->string('email');
    $table->string('passwordHash');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('locations', function (Blueprint $table) {
    $table->id();

    $table->float('latitude', 23, 20);
    $table->float('longitude', 23, 20);

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('types', function (Blueprint $table) {
    $table->id();

    $table->string('type');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('animals', function (Blueprint $table) {
    $table->id();

    $table->float('weight', 25, 10);
    $table->float('length', 25, 10);
    $table->float('height', 25, 10);

    /* Глянуть ограничения */
    $table->string('gender');
    $table->string('lifeStatus')->default('ALIVE');

    //Автоматом на момент добавления
    $table->timestamp('chippingDateTime')->default(Capsule::raw('CURRENT_TIMESTAMP'));

    /* Привязать к пользователю */
    $table->unsignedBigInteger('chipperId');
    $table->foreign('chipperId')
        ->references('id')->on('users');

    /* Привязать к локации */
    $table->unsignedBigInteger('chippingLocationId');
    $table->foreign('chippingLocationId')
        ->references('id')->on('locations');

    $table->timestamp('deathDateTime')->nullable()->default(null);

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('animals_locations', function(Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('animal_id')->unsigned();
    $table->unsignedBigInteger('location_id')->unsigned();
    $table->timestamp('dateTimeOfVisitLocationPoint')
        ->default(Capsule::raw('CURRENT_TIMESTAMP'));

    $table->foreign('animal_id')->references('id')
        ->on('animals')->onDelete('cascade');
    $table->foreign('location_id')->references('id')
        ->on('locations')->onDelete('cascade');

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('animals_types', function(Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('animal_id')->unsigned();
    $table->unsignedBigInteger('type_id')->unsigned();

    $table->foreign('animal_id')->references('id')
        ->on('animals')->onDelete('cascade');
    $table->foreign('type_id')->references('id')
        ->on('types')->onDelete('cascade');
});