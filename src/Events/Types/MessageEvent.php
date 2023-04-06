<?php

namespace Mateodioev\TgHandler\Event\Types;

use Mateodioev\TgHandler\Events\{abstractEvent, EventType};

/**
 * Event for incomming messages
 */
abstract class MessageEvent extends abstractEvent
{
    public EventType $type = EventType::message;
}
