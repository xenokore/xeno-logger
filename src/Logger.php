<?php

namespace Xenokore\Logger;

use Psr\Log\AbstractLogger;
use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;

use Xenokore\Utility\Helper\ArrayHelper;
use Xenokore\Utility\Helper\DirectoryHelper;
use Xenokore\Logger\Exception\InvalidLogDirectoryException;
use Monolog\Formatter\LineFormatter;

class Logger extends AbstractLogger
{
    public const FINGERS_CROSSED_LOG_FILENAME = 'error.debug.log';
    public const ERROR_LOG_FILENAME           = 'error.log';
    public const DEBUG_LOG_FILENAME           = 'debug.log';

    /**
     * The monolog instance
     *
     * @var MonoLogger
     */
    private $logger;

    public function __construct(array $config = [])
    {
        // Overwrite custom config over the default one
        $config = ArrayHelper::mergeRecursiveDistinct(
            include __DIR__ . '/../config/logger.conf.default.php',
            $config
        );

        $output_dir = $config['output_dir'];

        // Use system temp dir as default output dir
        if ($output_dir === null || $output_dir === false) {
            $output_dir = \sys_get_temp_dir() . '/xeno-logger';
        } else {
            // Normalize output dir path
            $output_dir = \rtrim(\trim($output_dir,'\\/'));
        }

        // Make sure output directory exists
        if (!DirectoryHelper::createIfNotExist($output_dir)) {
            throw new InvalidLogDirectoryException(
                "failed to create logger output dir: '{$output_dir}'"
            );
        }

        // Check if output directory is accessible
        if (!DirectoryHelper::isAccessible($output_dir)) {
            throw new InvalidLogDirectoryException(
                "'{$output_dir}' is not accessible"
            );
        }

        // Create the logger
        $this->logger = new MonoLogger('log');

        // Error StreamHandler
        $error_stream_handler = new StreamHandler(
            $output_dir . '/' . self::ERROR_LOG_FILENAME,
            MonoLogger::WARNING
        );
        if($config['add_line_formatter']){
            $error_stream_handler->setFormatter($this->getStacktraceFormatter());
        }
        $this->logger->pushHandler($error_stream_handler);

        // Add fingers-crossed handler
        // When an error occurs this handler will include all debug/info logs that came before it
        if ($config['use_fingers_crossed_log']) {
            $fc_stream_handler = new StreamHandler(
                $output_dir . '/' . self::FINGERS_CROSSED_LOG_FILENAME,
                MonoLogger::DEBUG,
                false
            );
            $this->logger->pushHandler(new FingersCrossedHandler($fc_stream_handler, MonoLogger::ERROR));
        }

        // Add debug level handler
        if ($config['use_debug_log']) {
            $debug_stream_handler = new StreamHandler(
                $output_dir . '/' . self::DEBUG_LOG_FILENAME,
                MonoLogger::DEBUG
            );
            if($config['add_line_formatter']){
                $debug_stream_handler->setFormatter($this->getStacktraceFormatter());
            }
            $this->logger->pushHandler($debug_stream_handler);
        }
    }

    private function getStacktraceFormatter()
    {
        // Monolog stacktrace formatter
        $formatter = new LineFormatter(
            LineFormatter::SIMPLE_FORMAT,
            LineFormatter::SIMPLE_DATE,
            true,
            true
        );
        return $formatter;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function addInfo(string $key, string $value): void
    {
        $this->logger->pushProcessor(
            function ($record) use ($key, $value) {
                $record['extra'][$key] = $value;
                return $record;
            }
        );
    }
}
