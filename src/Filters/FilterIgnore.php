<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * This filter always return false, use this filter to ignore a handler
 */
#[Attribute]

class FilterIgnore implements Filter
{
    public function apply(Context $ctx): bool
    {
        return false;
    }
}
