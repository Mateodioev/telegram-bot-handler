<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;

/**
 * Validate if the message is a sticker
 */
#[Attribute]
class FilterMessageMediaSticker extends FilterMessageMedia
{
    public function __construct()
    {
        parent::__construct(MediaType::sticker);
    }
}
