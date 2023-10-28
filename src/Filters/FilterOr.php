<?php

namespace Mateodioev\TgHandler\Filters;

use Mateodioev\TgHandler\Context;
use Attribute;

/**
 * Return true is `$a` or `$b` return true after call method {@see Filter::apply}
 */
#[Attribute]
final class FilterOr implements Filter
{
    public function __construct(
        private readonly Filter $a,
        private readonly Filter $b
    ) {
    }

    public function apply(Context $ctx): bool
    {
        return $this->a->apply($ctx) || $this->b->apply($ctx);
    }
}
