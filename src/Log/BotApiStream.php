<?php

namespace Mateodioev\TgHandler\Log;

use Mateodioev\Bots\Telegram\Api;

/**
 * Push messages to telegram channel/chat
 */
class BotApiStream implements Stream
{
    public function __construct(protected Api $api, protected string $chatId) {}

    public function push(string $message): void
    {
        $message = $this->addHtmlTags($this->replaceIlegalCharacters($message));

        $this->api->sendMessage(
            $this->chatId,
            $message,
            ['parse_mode' => 'html']
        );
    }

    protected function replaceIlegalCharacters(string $message): string
    {
        return str_replace(['<', '>'], ['&lt;', '&gt;'], $message);
    }

    public function addHtmlTags(string $input): string
    {
        return preg_replace('/\[(.*?)\]/', '<b>[$1]</b>', $input);
    }
}