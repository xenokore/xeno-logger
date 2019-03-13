<?php

namespace Xenokore\Logger\Tests;

use Xenokore\Logger\Logger;
use Xenokore\Logger\Exception\InvalidLogDirectoryException;

use Xenokore\Utility\Helper\DirectoryHelper;

use PHPUnit\Framework\TestCase;

use Psr\Log\AbstractLogger;

class LoggerTest extends TestCase
{
    private $temp_file_dir;

    public function __construct(...$args)
    {
        $temp_file_dir = __DIR__ . '/' . md5(time());

        if (!DirectoryHelper::create($temp_file_dir)) {
            throw new \Exception("failed to create temp test dir: {$temp_file_dir}");
        }

        $this->temp_file_dir = $temp_file_dir;

        parent::__construct(...$args);
    }

    public function __destruct()
    {
        if (!DirectoryHelper::delete($this->temp_file_dir)) {
            throw new \Exception("failed to delete temp test dir: {$this->temp_file_dir}");
        }

        parent::__destruct();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(AbstractLogger::class, new Logger());
    }
}
