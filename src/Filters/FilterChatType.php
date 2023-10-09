<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Validate if the chat type is the specified
 */
#[Attribute]
class FilterChatType implements Filter
{
    private string $chatType;

    public function __construct(string $chatType)
    {
        $this->chatType = $chatType;
    }

    public function apply(Context $ctx): bool
    {
        return $this->chatType === $ctx->getChatType();
    }
}