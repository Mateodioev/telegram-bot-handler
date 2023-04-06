<?php

namespace Mateodioev\TgHandler\Event\Types;

use Mateodioev\TgHandler\Events\{abstractEvent, EventType};

/**
 * Event for buttons pressed
 */
abstract class CallbackQueryEvent extends abstractEvent
{
    public EventType $type = EventType::callback_query;
}
