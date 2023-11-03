<?php

namespace Mateodioev\TgHandler\Events\Types;

use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

/**
 * This event can be used for all events
 */
abstract class AllEvent extends abstractEvent
{
    public EventType $type = EventType::all;

    public function isValid(): bool
    {
        return true;
    }
}
