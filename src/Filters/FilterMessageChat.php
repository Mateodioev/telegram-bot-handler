<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Validate if the message is from the chat id specified
 */
#[Attribute]
final readonly class FilterMessageChat implements Filter
{
    public function __construct(
        private int|string $chatId
    ) {
    }

    public function apply(Context $ctx): bool
    {
        $fromChat = $ctx->getChatId();

        return $this->chatId === $fromChat;
    }
}
