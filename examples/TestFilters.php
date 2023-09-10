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
    const CHAT_ID = 996202950; // Always set as INT

    public function execute(Api $bot, Context $context, array $args = [])
    {
        $message = $bot->replyTo(
            self::CHAT_ID, // this is equal to "$context->getChatId()" because this command only respond to the same chat id
            'Hi ' . ($context->getUser()?->mention() ?? 'Default name'),
            $context->getMessageId(),
            ['parse_mode' => 'HTML']
        );

        $this->logger()->debug('Result message of test filter: {msg}', [
            'msg' => $message->toString(JSON_PRETTY_PRINT)
        ]);
    }
}
