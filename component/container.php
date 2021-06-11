<?php

use Xenokore\Logger\Logger;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => function () {
        return new Logger(
            [
                'output_dir'              => $_ENV['APP_LOG_DIR'] ?? null,
                'use_debug_log'           => (bool) $_ENV['APP_LOG_DEBUG'] ?? false,
                'use_fingers_crossed_log' => (bool) $_ENV['APP_LOG_FINGERS_CROSSED'] ?? true,
            ]
        );
    },
];
