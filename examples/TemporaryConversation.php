<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Conversations\{MessageConversation, customTTl};

class TriggerConversationCommand extends MessageCommand
{
    protected string $name = 'temp';
    protected string $description = 'Trigger a temporary conversation';

    public function execute(array $args = [])
    {
        $this->api()->sendMessage(
            $this->ctx()->message()->chat()->id(),
            'Send me a message. You only have 1 hour to do it.'
        );

        $actualTime = time();

        // Expire the conversation after 1 hour
        return TemporaryConversation::fromContext($this->ctx())
            ->withCustomTTL(60 * 60)
            ->setCreatedAt($actualTime);
    }

}

class TemporaryConversation extends MessageConversation
{
    use customTTl;

    private int $createdAt;

    public function setCreatedAt(int $time): static
    {
        $this->createdAt = $time;
        return $this;
    }

    public function execute(array $args = [])
    {
        $this->api()->sendMessage(
            $this->ctx()->message()->chat()->id(),
            'You sent: ' . $this->ctx()->message()->text() . "\n\nSend me another message (expire in " . date('Y-m-d H:i:s', $this->createdAt) . ').'
        );

        /// With this, the conversation will expire after 1 hour from the creation time

        $diff = time() - $this->createdAt;

        return TemporaryConversation::fromContext($this->ctx())
            ->withCustomTTL(60 * 60 - $diff)
            ->setCreatedAt($this->createdAt);
    }

    public function onExpired(): void
    {
        $this->api()->sendMessage($this->chatId, 'The conversation has expired.');
    }
}
