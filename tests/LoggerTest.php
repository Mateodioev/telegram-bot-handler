<?php

namespace Tests;

use Mateodioev\TgHandler\Log\Logger;
use Mateodioev\TgHandler\Log\TerminalStream;
use PHPUnit\Framework\TestCase;

use function ob_start, call_user_func, ob_get_contents, ob_end_clean;

class LoggerTest extends TestCase
{
    public function testCreateLogger()
    {
        $this->assertInstanceOf(Logger::class, $this->logger());
    }

    public function testLogMessage()
    {
        $message = 'This is a message';
        $logger  = $this->logger();
        $loggers = $this->getLoggers($logger);

        foreach ($loggers as $loggerFn) {
            $output = $this->getStdOutput($loggerFn, $message);

            $this->assertNotEmpty($output);
            $this->assertTrue(\str_contains($output, $message));
        }
    }

    public function testDisableAllLogsLevels()
    {
        $message = 'This is a message';
        $logger  = $this->logger()->setLevel(Logger::ALL, false);
        $loggers = $this->getLoggers($logger);

        foreach ($loggers as $loggerFn) {
            $output = $this->getStdOutput($loggerFn, $message);

            $this->assertEmpty($output);
        }
    }

    public function testLogLevels()
    {
        $message = 'This is a message';
        $logger  = $this->logger();

        $logger->setLevel(Logger::ALL, false);
        $logger->setLevel(Logger::DEBUG); // enable only debug messages

        $output = $this->getStdOutput($logger->debug(...), $message);
        $this->assertNotEmpty($output);
    }

    protected function getLoggers(Logger $logger): array
    {
        return [
            $logger->emergency(...),
            $logger->alert(...),
            $logger->critical(...),
            $logger->error(...),
            $logger->warning(...),
            $logger->notice(...),
            $logger->info(...),
            $logger->debug(...)
        ];
    }

    protected function logger(): Logger
    {
        return new Logger(new TerminalStream);
    }

    protected function getStdOutput(\Closure $fn, ...$params)
    {
        ob_start();

        call_user_func_array($fn, $params);
        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
