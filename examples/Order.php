<?php

declare(strict_types=1);
use Mateodioev\TgHandler\Commands\MessageCommand;

class Order extends MessageCommand
{
    protected string $name = 'order';

    public function execute(array $args = [])
    {
        $this->api()->replyTo(
            $this->ctx()->getChatId(),
            'What do you want?',
            $this->ctx()->getMessageId(),
        );

        return OrderFSMConversation::fromContext($this->ctx());
    }
}
