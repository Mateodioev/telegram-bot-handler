<?php

namespace Mateodioev\TgHandler\Events\Types;

use Mateodioev\TgHandler\Events\{abstractEvent, EventType};

/**
 * Event for buttons pressed
 */
abstract class InlineQueryEvent extends abstractEvent
{
    public EventType $type = EventType::inline_query;
}
