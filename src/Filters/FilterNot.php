<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Negate the given filter
 */
#[Attribute]
class FilterNot implements Filter
{
    public function __construct(
        private readonly Filter $filter
    ) {
    }

    public function apply(Context $ctx): bool
    {
        return !$this->filter->apply($ctx);
    }
}
