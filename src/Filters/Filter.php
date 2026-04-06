<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Mateodioev\TgHandler\Context;

interface Filter
{
    /** Apply the current filter */
    public function apply(Context $ctx): bool;
}
