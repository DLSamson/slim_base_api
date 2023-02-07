<?php

use function DI\autowire;
use function DI\get;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;


return [
    Logger::class => autowire()
        ->constructor('main', [new StreamHandler(ROOT_DIR.'/log/log.log', Logger::DEBUG)]),

    LoggerInterface::class => get(Logger::class),
];

