<?php

use function DI\autowire;
use function DI\get;
use function DI\factory;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

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

