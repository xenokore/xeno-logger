<?php

use Xenokore\Logger\Logger;

use function DI\create;
use function DI\get;
use function DI\autowire;
use function DI\factory;

return [
    Logger::class => function ($container) {
        return new Logger(
            [
                'output_dir'              => \getenv('APP_LOG_DIR'),
                'use_debug_log'           => (bool) \getenv('APP_LOG_DEBUG'),
                'use_fingers_crossed_log' => 
                    // Check if ENV var is set, default to true otherwise
                    (\getenv('APP_LOG_FINGERS_CROSSED') !== false) ? 
                    (bool) \getenv('APP_LOG_FINGERS_CROSSED') :
                    true,
            ]
        );
    }
];
