<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\Bots\Telegram\Types\abstractType;
use Mateodioev\TgHandler\Context;

/**
 * Validate if the message is of the media type specified
 */
#[Attribute]
class FilterMessageMedia implements Filter
{
    private readonly MessageType $mediaType;

    public function __construct(
        MessageType $mediaType
    ) {
        $this->mediaType = $mediaType;
    }

    public function apply(Context $ctx): bool
    {
        return $ctx->{$this->mediaType->name} !== abstractType::DEFAULT_PARAM;
    }
}