<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Commands\MessageCommand;

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
        return NameConversation::fromContext($this->ctx());
    }
}
