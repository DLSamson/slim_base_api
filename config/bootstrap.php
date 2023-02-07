<?php

use DI\ContainerBuilder;


$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->addDefinitions(require 'container.php');
$container = $containerBuilder->build();

use DI\Bridge\Slim\Bridge;

$app = Bridge::create($container);