<?php

namespace Mateodioev\TgHandler\Events\Types;

use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

/**
 * Event for incomming messages
 */
abstract class MessageEvent extends abstractEvent
{
    public EventType $type = EventType::message;
}
