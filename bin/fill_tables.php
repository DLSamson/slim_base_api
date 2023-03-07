<?php

error_reporting(E_ERROR);
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Api\Core\Models\User;

$faker = Faker\Factory::create();

foreach(range(1, 20) as $index) {
    $user = new User([
        'firstName'    => $faker->firstName(),
        'lastName'     => $faker->lastName(),
        'email'        => $faker->unique()->email(),
        'passwordHash' => User::HashPassword($faker->password()),
    ]);

//    $user->save();

//    echo "$index: User created";
    echo $user->email . PHP_EOL;
}

echo "\nCOMPLETE";