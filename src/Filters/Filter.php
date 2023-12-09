<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

#[Attribute]
interface Filter
{
    /** Apply the current filter */
    public function apply(Context $ctx): bool;
}
