<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

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
