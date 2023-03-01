<?php

use function DI\autowire;
use function DI\get;
use function DI\factory;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

if(!is_dir(ROOT_DIR.'/cache'))
    mkdir(ROOT_DIR.'/cache');

if(!is_dir(ROOT_DIR.'/cache/templates'))
    mkdir(ROOT_DIR.'/cache/templates');

if(!is_dir(ROOT_DIR.'/log'))
    mkdir(ROOT_DIR.'/log');

return [
        Logger::class => autowire()
            ->constructor('main', [new StreamHandler(ROOT_DIR.'/log/log.log', Logger::DEBUG)]),

        LoggerInterface::class => get(Logger::class),

        Fenom::class => factory(function(ContainerInterface $c) {
            return Fenom::factory(
                ROOT_DIR.'/templates',
                ROOT_DIR.'/cache/templates',
                Fenom::AUTO_RELOAD | Fenom::AUTO_STRIP | Fenom::AUTO_ESCAPE);
        }),
];

