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

    $table->double('latitude');
    $table->double('longitude');

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

    $table->float('weight');
    $table->float('length');
    $table->float('height');

    /* Глянуть ограничения */
    $table->string('gender');
    $table->string('lifeStatus');

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

    $table->date('deathDateTime')->nullable();

    $table->timestamps();
    $table->softDeletes();
});

Capsule::schema()->create('animals_locations', function(Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('animal_id')->unsigned();
    $table->unsignedBigInteger('location_id')->unsigned();

    $table->foreign('animal_id')->references('id')
        ->on('animals')->onDelete('cascade');
    $table->foreign('location_id')->references('id')
        ->on('locations')->onDelete('cascade');
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