<?php

use Mateodioev\TgHandler\Events\Types\MessageEvent;

#[\Mateodioev\TgHandler\Filters\FilterMessageRegex(pattern: '/^Hello$/i')]
class Message extends MessageEvent
{
    public function execute(array $args = [])
    {
        // handle all message events

        $this->api()->replyTo($this->ctx()->getChatId(), 'Hi!', $this->ctx()->getMessageId());
    }
}
