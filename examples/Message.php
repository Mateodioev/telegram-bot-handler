<?php

use Mateodioev\TgHandler\Events\Types\MessageEvent;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

class Message extends MessageEvent
{
	public function execute(array $args = [])
    {
        // handle all message events

        if ($this->ctx()->getMessageText() == 'Hello') {
            $this->api()->replyTo($this->ctx()->getChatId(), 'Hi!', $this->ctx()->getMessageId());
        }
	}
}