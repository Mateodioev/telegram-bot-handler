<?php

namespace Mateodioev\TgHandler\Log;

use Mateodioev\Bots\Telegram\Api;

use function str_replace, preg_replace;

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

    public function push(string $message, ?string $level = null): void
    {
        if ($this->isFlagSet(Logger::levelToInt($level ?? '')) === false)
            return;

        $message = $this->addHtmlTags($this->replaceIllegalCharacters($message));

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

    public function addHtmlTags(string $input): string
    {
        return preg_replace('/\[(.*?)\]/', '<b>[$1]</b>', $input);
    }
}