<?php

namespace Tests\FakeCtx;

use Mateodioev\TgHandler\Context;

abstract class ctx extends Context
{
    /**
     * Create new custom context
     */
    abstract public static function new(): static;
}
