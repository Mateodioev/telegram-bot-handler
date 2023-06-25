<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\MessageConversation;

class nameConversation extends MessageConversation
{
    protected string $format = 'My name is {w:name}';
    public function execute(Api $bot, Context $context, array $args = [])
    {
        $bot->sendMessage(
            $context->getChatId(),
            'Nice to meet you ' . $this->param('name')
        );
    }
}