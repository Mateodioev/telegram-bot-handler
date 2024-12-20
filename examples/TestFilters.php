<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Events\Types\MessageEvent;
use Mateodioev\TgHandler\Filters\{FilterMessageChat, FilterMessageRegex};

/**
 * This event only works on chat id `TestFilters::CHAT_ID` and with messages containing the word "filters"
 */
#[FilterMessageChat(TestFilters::CHAT_ID), FilterMessageRegex('/.*(filters).*/i')]
class TestFilters extends MessageEvent
{
    public const CHAT_ID = 996202950;

    public function execute(array $args = [])
    {
        $message = $this->api()->replyTo(
            self::CHAT_ID, // this is equal to "$context->getChatId()" because this command only respond to the same chat id
            'Hi ' . ($this->ctx()->getUser()?->mention() ?? 'Default name'),
            $this->ctx()->getMessageId(),
        );

        $this->logger()->debug('Result message of test filter: {msg}', [
            'msg' => $message->toString()
        ]);
    }
}
