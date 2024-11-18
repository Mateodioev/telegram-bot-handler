<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;

/**
 * Validate if the chat type is private
 */
#[Attribute]
final class FilterPrivateChat extends FilterChatType
{
    public function __construct()
    {
        parent::__construct('private');
    }
}
