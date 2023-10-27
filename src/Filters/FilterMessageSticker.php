<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;

/**
 * Validate if the message is a sticker
 */
#[Attribute]
class FilterMessageSticker extends FilterMessageMedia
{
    public function __construct()
    {
        parent::__construct(MediaType::sticker);
    }
}
