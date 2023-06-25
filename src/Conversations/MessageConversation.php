<?php

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\TgHandler\Events\EventType;

abstract class MessageConversation extends ConversationHandler
{
    public static function new(int $chatId, int $userId): static
    {
        return self::create(EventType::message, $chatId, $userId);
    }
}