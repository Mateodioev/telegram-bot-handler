<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\EventType;

abstract class MessageConversation extends ConversationHandler
{
    public static function new(int $chatId, int $userId): static
    {
        return self::create(EventType::message, $chatId, $userId);
    }

    /**
     * Create a new instance from context
     *
     * @see MessageConversation::new()
     */
    public static function fromContext(Context $ctx): static
    {
        return self::new($ctx->getChatId(), $ctx->getUserId());
    }
}
