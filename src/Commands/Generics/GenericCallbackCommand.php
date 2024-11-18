<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands\Generics;

use Mateodioev\TgHandler\Events\EventType;

class GenericCallbackCommand extends GenericCommand
{
    public EventType $type = EventType::callback_query;
}
