<?php

declare(strict_types=1);

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Context;

class Name extends MessageCommand
{
    protected string $name = 'name';

    public function execute(array $args = [])
    {
        $this->api()->replyTo(
            $this->ctx()->getChatId(),
            'Please give me your name:',
            $this->ctx()->getMessageId(),
        );

        // Register next conversation handler
        return nameConversation::fromContext($this->ctx());
    }

    /**
     * @deprecated
     */
    public function handle(Api $bot, Context $context, array $args = [])
    {
        $bot->replyTo(
            $context->getChatId(),
            'Please give me your name:',
            $context->getMessageId(),
        );

        // Register next conversation handler
        return nameConversation::fromContext($context);
    }
}
