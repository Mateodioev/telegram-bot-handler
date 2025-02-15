<?php

declare(strict_types=1);

namespace Tests;

use Mateodioev\TgHandler\Bot;
use Psr\Log\NullLogger;

class EmptyBot extends Bot
{
    public function __construct()
    {
        parent::__construct('FAKE TOKEN', new NullLogger());
    }
}
