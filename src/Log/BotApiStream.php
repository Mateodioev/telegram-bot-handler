<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Types\InputFile;
use SimpleLogger\streams\LogResult;

use function Amp\File\{deleteFile, write};

/**
 * Push messages to telegram channel/chat
 */
class BotApiStream implements Stream
{
    use BitwiseFlag;

    public function __construct(
        private BotApiStreamConfig $config,
    ) {
        $this->setLevel(Logger::CRITICAL | Logger::ERROR | Logger::EMERGENCY);
    }

    public function setLevel(int $level, bool $add = true): static
    {
        $this->setFlag($level, $add);
        return $this;
    }

    public function push(LogResult $message, ?string $level = null): void
    {
        if ($this->isFlagSet(Logger::levelToInt($message->level ?? '')) === false) {
            return;
        }

        if (
            ($message->exception !== null || strlen($message->message) > 1000)
            && $this->config->sendFiles
        ) {
            $this->sendMessageAsFile($message);
            return;
        }

        $this->sendSingleMessage($message);
    }

    private function sendMessageAsFile(LogResult $message): void
    {
        $file = $this->config->getFileName($message);
        write($file, $this->config->getFileContent($message));

        $caption = $this->formatMessage($message);
        $api = $this->config->getApi();

        $api->sendDocument(
            $this->config->channelID,
            InputFile::fromLocal($file),
            [
                'caption' => $caption,
                'parse_mode' => 'html',
            ]
        );

        deleteFile($file);
    }

    private function sendSingleMessage(LogResult $message): void
    {
        $message = $this->formatMessage($message);
        (new Api($this->config->token))
            ->sendMessage(
                $this->config->channelID,
                $message,
                ['parse_mode' => 'html']
            );
    }

    private function formatMessage(LogResult $message): string
    {
        $formatter = $this->config->messageFormat;
        return $formatter($message);
    }
}
