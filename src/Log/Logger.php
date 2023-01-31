<?php

namespace Mateodioev\TgHandler\Log;

use Psr\Log\{AbstractLogger, LoggerInterface};
use InvalidArgumentException;

class Logger extends AbstractLogger implements LoggerInterface
{

    public function __construct(private readonly Stream $stream) {}

    /**
     * @inheritDoc
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        $logMessage = "[%s] [%s] %s" . PHP_EOL;

        $this->stream->push(sprintf($logMessage,
            $date,
            strtoupper($level),
            $this->makeLogMessage($message, $context))
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function makeLogMessage(string $message, array $context = []): string
    {
        $pattern = '/{([a-zA-Z0-9_]+)}/';
        return preg_replace_callback($pattern, function ($matches) use ($context) {
            return $context[$matches[1]] ?? $matches[0];
        }, $message);
    }
}