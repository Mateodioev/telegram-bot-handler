<?php

namespace Mateodioev\TgHandler\Log;

use DateTime, Stringable;
use Psr\Log\{AbstractLogger, InvalidArgumentException as LogInvalidArgumentException, LoggerInterface, LogLevel};
use Smoren\StringFormatter\{StringFormatter, StringFormatterException};

use function preg_replace;
use function strtoupper;

class Logger extends AbstractLogger implements LoggerInterface
{
    use BitwiseFlag;

    public const ALL = 255;
    public const CRITICAL = 128;
    public const ERROR = 64;
    public const EMERGENCY = 32;
    public const ALERT = 16;
    public const WARNING = 8;
    public const NOTICE = 4;
    public const INFO = 2;
    public const DEBUG = 1;

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
    public function log($level, Stringable|string $message, array $context = []): void
    {
        if (!$this->canAccess(self::levelToInt($level))) {
            return;
        }

        $date = (new DateTime())->format('Y-m-d H:i:s');

        try {
            $logMessage = StringFormatter::format(self::$messageFormat, [
                'time' => $date,
                'level' => strtoupper($level),
                'message' => $this->makeLogMessage($message, $context),
                'EOL' => PHP_EOL
            ]);

            $this->stream->push($logMessage, $level);
        } catch (StringFormatterException $th) {
            throw new LogInvalidArgumentException($th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * @throws StringFormatterException
     */
    protected function makeLogMessage(string $message, array $context = []): string
    {
        // if context is empty, delete brackets
        if (empty($context)) {
            return $this->deleteBrackets($message);
        }

        return StringFormatter::format($message, $context);
    }

    protected function deleteBrackets(string $message): string
    {
        return preg_replace('/\{(.*)\}/', '$1', $message);
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
