<?php

namespace Xenokore\Logger\Tests;

use Psr\Log\AbstractLogger;
use Xenokore\Logger\Logger;
use Xenokore\Logger\Exception\InvalidLogDirectoryException;
use Xenokore\Utility\Helper\DirectoryHelper;

use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        foreach(\glob(__DIR__ . '/data/*.log') as $log_file){
            \unlink($log_file);
        }
    }

    public function testInstance()
    {
        $this->assertInstanceOf(AbstractLogger::class, new Logger());
    }

    public function testLogger()
    {
        $error_log_file           = __DIR__ . '/data/' . Logger::ERROR_LOG_FILENAME;
        $debug_log_file           = __DIR__ . '/data/' . Logger::DEBUG_LOG_FILENAME;
        $fingers_crossed_log_file = __DIR__ . '/data/' . Logger::FINGERS_CROSSED_LOG_FILENAME;

        $logger = new Logger([
            'output_dir'              => __DIR__ . '/data',
            "use_debug_log"           => true,
            "use_fingers_crossed_log" => true,
        ]);

        $logger->debug('TEST_DEBUG_TEXT');
        $logger->error('TEST_ERROR_TEXT');

        $this->assertTrue(\file_exists($debug_log_file));
        $debug_log_contents = \file_get_contents($debug_log_file);
        $this->assertTrue(\strpos($debug_log_contents, 'TEST_DEBUG_TEXT') !== false);

        $this->assertTrue(\file_exists($error_log_file));
        $error_log_contents = \file_get_contents($error_log_file);
        $this->assertTrue(\strpos($error_log_contents, 'TEST_ERROR_TEXT') !== false);

        // FingersCrossed log must contain both strings
        $this->assertTrue(\file_exists($fingers_crossed_log_file));
        $fingers_crossed_log_contents = \file_get_contents($fingers_crossed_log_file);
        $this->assertTrue(\strpos($fingers_crossed_log_contents, 'TEST_DEBUG_TEXT') !== false);
        $this->assertTrue(\strpos($fingers_crossed_log_contents, 'TEST_ERROR_TEXT') !== false);
    }
}
