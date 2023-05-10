<?php

namespace Mateodioev\TgHandler\Events\Types;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\{abstractEvent, EventType};

/**
 * Event for buttons pressed
 */
abstract class AllEvent extends abstractEvent
{
    public EventType $type = EventType::all;

    public function isValid(Api $bot, Context $context): bool
    {
        return true;
    }
}
