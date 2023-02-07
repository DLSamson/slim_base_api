<?php

use Api\Controllers\EchoController;

/* @var \Slim\App $app */
$app->get('/', [EchoController::class, 'echo']);