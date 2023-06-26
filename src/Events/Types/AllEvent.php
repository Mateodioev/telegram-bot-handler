<?php

namespace Mateodioev\TgHandler\Events\Types;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\{abstractEvent, EventType};

/**
 * This event can be used for all events
 */
abstract class AllEvent extends abstractEvent
{
    public EventType $type = EventType::all;

    public function isValid(Api $bot, Context $context): bool
    {
        return true;
    }
}