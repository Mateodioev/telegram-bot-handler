<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\Types\MessageEvent;
use Mateodioev\TgHandler\Filters\{FilterMessageChat, FilterMessageRegex};

/**
 * This event only works on chat id `TestFilters::CHAT_ID` and with messages containing the word "filters"
 */
#[FilterMessageChat(TestFilters::CHAT_ID), FilterMessageRegex('/.*(filters).*/i')]
class TestFilters extends MessageEvent
{
    const CHAT_ID = 996202950; // If you use chat id, always set as INT

    public function execute(Api $bot, Context $context, array $args = [])
    {
        $result = $bot->replyTo(
            self::CHAT_ID, // this is equal to "$context->getChatId()" because this command only respond to the same chat id
            'Hi ' . ($context->getUser()?->mention() ?? 'Default name'),
            $context->getMessageId()
        );

        $this->logger()->debug(json_encode($result->getReduced(), JSON_PRETTY_PRINT));
    }
}
