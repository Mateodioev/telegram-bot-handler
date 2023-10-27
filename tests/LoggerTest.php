<?php

namespace Tests;

use Mateodioev\TgHandler\Log\{Logger, ResourceStream};
use PHPUnit\Framework\TestCase;

use function fopen;

class LoggerTest extends TestCase
{
    private static $stream = null;

    /**
     * @return resource
     */
    public static function streamResource()
    {
        if (self::$stream === null) {
            self::$stream = fopen('php://memory', 'a+');
        }

        return self::$stream;
    }

    public function testCreateLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->logger());
    }

    protected function logger(): Logger
    {
        return new Logger(
            new ResourceStream(self::streamResource())
        );
    }

    public function testLogMessage()
    {
        $message = 'This is a message';
        $logger  = $this->logger()->setLevel(Logger::ALL);

        $loggers = $this->getLoggers();

        foreach ($loggers as $level) {
            $output = $this->getStdOutput($logger, $level, $message);
            // var_dump($output);

            $this->assertNotEmpty($output, $level);
            $this->assertTrue(\str_contains($output, $message), $level);
        }
    }

    public function testDisableAllLogsLevels()
    {
        $message = 'This is a message';
        $logger  = $this->logger()->setLevel(Logger::ALL, false);
        $loggers = $this->getLoggers();

        foreach ($loggers as $level) {
            $output = $this->getStdOutput($logger, $level, $message);

            $this->assertEmpty($output, $level);
        }
    }

    public function testLogLevels()
    {
        $message = 'This is a message';
        $logger  = $this->logger();

        $logger->setLevel(Logger::ALL, false);
        $logger->setLevel(Logger::DEBUG); // enable only debug messages

        $output = $this->getStdOutput($logger, 'debug', $message);
        $this->assertNotEmpty($output);
    }

    protected function getLoggers(): array
    {
        return [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug'
        ];
    }

    protected function getStdOutput(Logger $logger, string $level, string $message): string
    {
        $logger->log($level, $message);
        rewind(self::streamResource());
        $content = stream_get_contents(self::streamResource());
        ftruncate(self::streamResource(), 0);

        return $content;
    }
}
