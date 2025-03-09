<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Events\EventType;
use Mateodioev\TgHandler\Events\Types\AllEvent;

/**
 * AllEvent receive all event types
 */
class All extends AllEvent
{
    // Set none to disable the event
    public EventType $type = EventType::none;

    public function execute(array $args = []): void
    {
        $type = $this->ctx()->eventType()->prettyName();
        $raw = json_encode($this->ctx()->getReduced(), JSON_PRETTY_PRINT) . PHP_EOL;

        $this->logger()->info('Receive new {type} event', ['type' => $type]);
        $this->logger()->info('Update: {raw}', ['raw' => $raw]);
    }
}
