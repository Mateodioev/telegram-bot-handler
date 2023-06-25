<?php

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\TgHandler\Events\{abstractEvent, EventType};
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

abstract class ConversationHandler extends abstractEvent implements Conversation
{
    protected function __construct(
        private int $chatId,
        private int $userId,
    ) {
    }

    protected static function create(EventType $type, int $chatId, int $userId): static
    {
        return (new static($chatId, $userId))
            ->setType($type);
    }

    public function isValid(Api $bot, Context $context): bool
    {
        return 1 === 1
            && $this->chatId === $context->getChatId()
            && $this->userId === $context->getUserId()
            && $this->type() === $context->eventType();
    }

    abstract public function execute(Api $bot, Context $context, array $args = []);

    /**
     * Set event type
     */
    protected function setType(EventType $type): static
    {
        $this->type = $type;
        return $this;
    }
}