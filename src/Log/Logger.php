<?php

namespace Mateodioev\TgHandler\Log;

use Psr\Log\{AbstractLogger, InvalidArgumentException as LogInvalidArgumentException, LoggerInterface, LogLevel};
use Smoren\StringFormatter\{StringFormatter, StringFormatterException};

class Logger extends AbstractLogger implements LoggerInterface
{
    use BitwiseFlag;

    const ALL = 255;
    const CRITICAL = 128;
    const ERROR = 64;
    const EMERGENCY = 32;
    const ALERT = 16;
    const WARNING = 8;
    const NOTICE = 4;
    const INFO = 2;
    const DEBUG = 1;

    public static string $messageFormat = "[{time}] [{level}] {message} {EOL}";

    public function __construct(private readonly Stream $stream)
    {
        $this->setLevel(self::ALL);
    }

    /**
     * Set log level
     */
    public function setLevel(int $level, bool $add = true): static
    {
        $this->setFlag($level, $add);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if (!$this->canAccess(self::levelToInt($level)))
            return;

        $date = (new \DateTime())->format('Y-m-d H:i:s');

        try {
            $logMessage = StringFormatter::format(self::$messageFormat, [
                'time' => $date,
                'level' => \strtoupper($level),
                'message' => $this->makeLogMessage($message, $context),
                'EOL' => PHP_EOL
            ]);

            $this->stream->push($logMessage);
        } catch (StringFormatterException $th) {
            throw new LogInvalidArgumentException($th->getMessage(), $th->getCode(), $th);
        }
    }

    protected function makeLogMessage(string $message, array $context = []): string
    {
        // if context is empty, delete brackets
        if (empty($context))
            return $this->deleteBrackets($message);

        return StringFormatter::format($message, $context);
    }

    protected function deleteBrackets(string $message): string
    {
        return \preg_replace('/\{(.*)\}/', '$1', $message);
    }
    /**
     * Return true if log level can access
     */
    protected function canAccess(int $level): bool
    {
        return $this->isFlagSet($level);
    }

    /**
     * Convert level string to int
     */
    public static function levelToInt(string $level): int
    {
        return match ($level) {
            LogLevel::EMERGENCY => self::EMERGENCY,
            LogLevel::ALERT => self::ALERT,
            LogLevel::CRITICAL => self::CRITICAL,
            LogLevel::ERROR => self::ERROR,
            LogLevel::WARNING => self::WARNING,
            LogLevel::NOTICE => self::NOTICE,
            LogLevel::INFO => self::INFO,
            LogLevel::DEBUG => self::DEBUG,
            default => self::ALL
        };
    }
}