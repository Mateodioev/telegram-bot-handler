<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Collection of filters
 * @internal
 */
#[Attribute]
final class FilterCollection implements Filter
{
    private array $filters;

    public function __construct(Filter ...$filters)
    {
        $this->filters = $filters;
    }

    /**
     * Apply all filters
     */
    public function apply(Context $ctx): bool
    {
        // array_reduce($this->filters, fn (Filter $filter) => $filter->apply($ctx));
        foreach ($this->filters as $filter) {
            if ($filter->apply($ctx) === false) {
                return false;
            }
        }

        return true;
    }
}
