<?php

namespace Mateodioev\TgHandler\Log;

use Psr\Log\{AbstractLogger, InvalidArgumentException as LogInvalidArgumentException, LoggerInterface};
use Smoren\StringFormatter\{StringFormatter, StringFormatterException};

class Logger extends AbstractLogger implements LoggerInterface
{
    public static string $messageFormat = "[{time}] [{level}] {message} {EOL}";

    public function __construct(private readonly Stream $stream) {}

    /**
     * @inheritDoc
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');

        try {
            $logMessage = StringFormatter::format(self::$messageFormat, [
                'time'    => $date,
                'level'   => \strtoupper($level),
                'message' => $this->makeLogMessage($message, $context),
                'EOL'     => PHP_EOL
            ]);
            $this->stream->push($logMessage);

        } catch (StringFormatterException $th) {
            throw new LogInvalidArgumentException($th->getMessage(), $th->getCode(), $th);
        }
    }

    protected function makeLogMessage(string $message, array $context = []): string
    {
        return StringFormatter::format($message, $context);
    }
}