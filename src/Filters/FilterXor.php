<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Return true is `$a` or `$b` return true after call method {@see Filter::apply}, but if both return true, `apply` return false
 */
#[Attribute]
final readonly class FilterXor implements Filter
{
    public function __construct(
        private Filter $a,
        private Filter $b
    ) {
    }

    public function apply(Context $ctx): bool
    {
        return $this->a->apply($ctx) xor $this->b->apply($ctx);
    }
}
