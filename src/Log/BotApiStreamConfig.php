<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use Closure;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Exception\TelegramParamException;
use SimpleLogger\streams\LogResult;

use function date;
use function sprintf;
use function strtoupper;

final class BotApiStreamConfig
{
    /**
     * @var Closure(string $logDir, string $level, int $timestamp):string $fileFormat Function to get the log message
     */
    private Closure $fileFormat;

    /**
     * @var Closure(LogResult $message):string $messageFormat Function to format the log message
     */
    public Closure $messageFormat;

    /**
     * @var Closure(LogResult $message):string $fileContentFormat Function to get the content of the log file
     */
    private Closure $fileContentFormat;

    /**
     * @param string $token Bot token from BotFather
     * @param string $channelID Channel ID or chat ID to send the logs
     * @param string $logDir Directory to save the log files
     * @param bool $sendFiles `true` to send log files, `false` to send the log message directly
     */
    public function __construct(
        public string $token,
        public string $channelID,
        public string $logDir,
        public bool $sendFiles = true,
        ?Closure $fileFormat = null,
        ?Closure $messageFormat = null,
        ?Closure $fileContentFormat = null,
    ) {
        $this->fileFormat = $fileFormat ?? $this->defaultGetFileNameFormatter(...);
        $this->messageFormat = $messageFormat ?? $this->defaultGetMessageFormatter(...);
        $this->fileContentFormat = $fileContentFormat ?? $this->defaultFileContentFormatter(...);
    }

    public static function default(
        string $token,
        string $channelID,
        string $logDir = '/tmp',
    ): self {
        return new self($token, $channelID, $logDir);
    }

    /**
     * @throws TelegramParamException
     */
    public function getApi(): Api
    {
        return new Api($this->token);
    }

    public function withToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function withChannelID(string $channelID): self
    {
        $this->channelID = $channelID;
        return $this;
    }

    public function withLogDir(string $logDir): self
    {
        $this->logDir = $logDir;
        return $this;
    }

    public function enableSendFiles(): self
    {
        $this->sendFiles = true;
        return $this;
    }

    public function disableSendFiles(): self
    {
        $this->sendFiles = false;
        return $this;
    }

    public function withFileFormat(Closure $fileFormat): self
    {
        $this->fileFormat = $fileFormat;
        return $this;
    }

    public function withMessageFormat(Closure $messageFormat): self
    {
        $this->messageFormat = $messageFormat;
        return $this;
    }

    public function withFileContentFormat(Closure $fileContentFormat): self
    {
        $this->fileContentFormat = $fileContentFormat;
        return $this;
    }

    public function getFileName(LogResult $logResult): string
    {
        $formatter = $this->fileFormat;
        return $formatter($this->logDir, $logResult->level, $logResult->timestamp);
    }

    public function getFileContent(LogResult $logResult): string
    {
        $formatter = $this->fileContentFormat;
        return $formatter($logResult);
    }

    private function defaultGetFileNameFormatter(string $logDir, string $level, int $timestamp): string
    {
        return sprintf(
            "%s/%s-%s.log",
            $logDir,
            strtoupper($level),
            date('Y-m-d', $timestamp)
        );
    }

    private function defaultGetMessageFormatter(LogResult $message): string
    {
        $level = $message->level;
        $levelEmoji = $this->getLevelEmoji($message->level);
        $timestamp = date('Y-m-d H:i:s', $message->timestamp);
        $message = $message->message;

        return "<b>$levelEmoji $level</b>\n" .
            "📅 <code>$timestamp</code>\n" .
            "📝 Message:\n<pre>$message</pre>";
    }

    private function defaultFileContentFormatter(LogResult $message): string
    {
        $content = "=== Log Details ===\n";
        $content .= 'Level: ' . $message->level . PHP_EOL;
        $content .= 'Time: ' . date('Y-m-d H:i:s', $message->timestamp) . PHP_EOL;
        $content .= 'Message: ' . $message->message . PHP_EOL . PHP_EOL;

        if ($message->exception === null) {
            return $content;
        }

        $content .= "=== Exception Details ===\n";
        $content .= 'Type: ' . $message->exception::class . PHP_EOL;
        $content .= 'Location: ' . $message->exception->getFile() . ':' . $message->exception->getLine() . PHP_EOL;
        $content .= 'Message: ' . $message->exception->getMessage() . PHP_EOL;
        $content .= 'Code: ' . ($message->exception->getCode() ?: 'unknown') . PHP_EOL . PHP_EOL;

        $content .= "=== Stack Trace ===\n";
        $content .= $message->exception->getTraceAsString() . PHP_EOL . PHP_EOL;

        if ($message->exception->getPrevious()) {
            $content .= "=== Previous Exception ===\n";
            $content .= 'Message: ' . $message->exception->getPrevious()->getMessage() . PHP_EOL;
            $content .= 'Trace: ' . $message->exception->getPrevious()->getTraceAsString() . PHP_EOL;
        }

        return $content;
    }

    private function getLevelEmoji(string $level): string
    {
        return match (strtoupper($level)) {
            'EMERGENCY' => '🚨',
            'ALERT' => '⚠️',
            'CRITICAL' => '❌',
            'ERROR' => '🔴',
            'WARNING' => '⚡',
            'NOTICE' => '📢',
            'INFO' => 'ℹ️',
            'DEBUG' => '🔍',
            default => '🔔'
        };
    }
}
