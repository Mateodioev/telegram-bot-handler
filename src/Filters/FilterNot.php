<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Negate the given filter
 */
#[Attribute]
readonly class FilterNot implements Filter
{
    public function __construct(
        private Filter $filter
    ) {
    }

    public function apply(Context $ctx): bool
    {
        return !$this->filter->apply($ctx);
    }
}
