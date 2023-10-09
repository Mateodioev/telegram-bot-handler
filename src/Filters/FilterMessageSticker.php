<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

#[Attribute]
class FilterMessageSticker implements Filter
{
    private Filter $filter;

    public function __construct()
    {
        $this->filter = new FilterMessageMedia(MediaType::sticker);
    }

    public function apply(Context $ctx): bool
    {
        return $this->filter->apply($ctx);
    }
}