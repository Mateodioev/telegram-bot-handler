<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\Bots\Telegram\Types\abstractType;
use Mateodioev\TgHandler\Context;

#[Attribute]
class FilterMessageMedia implements Filter
{
    public function __construct(
        private readonly MediaType $mediaType
    ) {
    }

    public function apply(Context $ctx): bool
    {
        return $ctx->{$this->mediaType->name} !== abstractType::DEFAULT_PARAM;
    }
}