<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use Mateodioev\Bots\Telegram\Api;
use SimpleLogger\streams\LogResult;

use function str_replace;

/**
 * Push messages to telegram channel/chat
 */
class BotApiStream implements Stream
{
    use BitwiseFlag;

    public function __construct(
        protected Api $api,
        protected string $chatId
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

        // $message = $this->addHtmlTags($this->replaceIllegalCharacters($message));
        $level = strtoupper($message->level);
        $strMessage = $this->replaceIllegalCharacters($message->message);
        $message = "<b>{$level}</b>\n<pre>{$strMessage}</pre>";

        $this->api->sendMessage(
            $this->chatId,
            $message,
            ['parse_mode' => 'html']
        );
    }

    protected function replaceIllegalCharacters(string $message): string
    {
        return str_replace(['<', '>'], ['&lt;', '&gt;'], $message);
    }
}
