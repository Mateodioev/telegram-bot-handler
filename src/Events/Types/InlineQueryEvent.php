<?php

namespace Mateodioev\TgHandler\Events\Types;

use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

/**
 * Event for buttons pressed
 */
abstract class InlineQueryEvent extends abstractEvent
{
    public EventType $type = EventType::inline_query;
}
