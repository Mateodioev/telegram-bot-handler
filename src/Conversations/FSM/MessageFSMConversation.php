<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\EventType;

abstract class MessageFSMConversation extends FSMConversation
{
    protected function __construct(int $chatId, int $userId)
    {
        parent::__construct($chatId, $userId, EventType::message);
    }

    public static function new(int $chatId, int $userId): static
    {
        return new static($chatId, $userId);
    }

    public static function fromContext(Context $ctx): static
    {
        return static::new($ctx->getChatId(), $ctx->getUserId());
    }
}
