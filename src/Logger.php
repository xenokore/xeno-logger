<?php

namespace Xenokore\Logger;

use Psr\Log\AbstractLogger;
use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;

use Xenokore\Utility\Helper\ArrayHelper;
use Xenokore\Utility\Helper\DirectoryHelper;

use Xenokore\Logger\Exception\InvalidLogDirectoryException;

/**
 * What we need as configuration:
 * - log output dir
 * - debugging or not
 */

class Logger extends AbstractLogger
{
    private const ERROR_LOG_FILENAME = 'error.log';
    private const DEBUG_LOG_FILENAME = 'debug.log';

    /**
     * The monolog instance
     * @var MonoLogger
     */
    private $logger;

    /**
     * The active configuration of the logger
     * @var \ArrayObject|array
     */
    private $config;

    public function __construct(array $config = [])
    {
        // Create the configuration
        $this->config = ArrayHelper::mergeRecursiveDistinct(
            require __DIR__ . '/../config/logger.conf.default.php',
            $config
        );

        // Config
        $debug              = (bool) ($this->config['debug'] ?? false);
        $logfile_enabled    = (bool) ($this->config['logfile']['enabled'] ?? false);
        $logfile_output_dir = $this->config['logfile']['output_dir'] ?? null;

        // Create the logger
        $this->logger = new MonoLogger('main');

        // Check if we need to add file output streams
        if (!$logfile_enabled) {
            return;
        }

        // Check if output directory is set
        if (is_null($logfile_output_dir) || !is_string($logfile_output_dir)) {
            throw new InvalidLogDirectoryException("logfile output directory must be set to a valid path");
        }

        // Check if output directory can be written to
        if (DirectoryHelper::isAccessible($logfile_output_dir)) {
            throw new InvalidLogDirectoryException("{$logfile_output_dir} is not a writable path");
        }

        // Add normal log file stream
        $this->logger->pushHandler(
            new StreamHandler(
                $logfile_output_dir . '/' . self::ERROR_LOG_FILENAME,
                MonoLogger::WARNING
            )
        );

        // Add debug log file stream
        if ($debug) {
            $this->logger->pushHandler(
                new StreamHandler(
                    $logfile_output_dir . '/' . self::DEBUG_LOG_FILENAME,
                    MonoLogger::DEBUG
                )
            );
        }
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function addInfo(string $key, string $value): void
    {
        $this->logger->pushProcessor(function ($record) use ($key, $value) {
            $record['extra'][$key] = $value;
            return $record;
        });
    }
}
