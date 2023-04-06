<?php

use Mateodioev\TgHandler\Events\Types\MessageEvent;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

class Message extends MessageEvent
{
	public function execute(Api $bot, Context $context, array $args = [])
    {
        // handle all message events

        if ($context->getMessageText() == 'Hello') {
            $bot->replyTo($context->getChatId(), 'Hi!', $context->getMessageId());
        }
	}
}