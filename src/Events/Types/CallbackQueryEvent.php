<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Events\Types;

use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

/**
 * Event for buttons pressed
 */
abstract class CallbackQueryEvent extends abstractEvent
{
    public EventType $type = EventType::callback_query;
}
