<?php

namespace \Xenokore\Logger;

use function DI\create;
use function DI\get;
use function DI\autowire;
use function DI\factory;

return [
    Logger::class => function ($container) {
        return new Logger($container->get('config')['logger'] ?? []);
    }
];
