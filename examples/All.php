<?php

use Mateodioev\TgHandler\Events\Types\AllEvent;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

/**
 * AllEvent receive all event types
 */
class All extends AllEvent
{
    public function execute(array $args = [])
    {
        $type = $this->ctx()->eventType()->prettyName();
        $raw = $this->ctx()->toString(JSON_PRETTY_PRINT) . PHP_EOL;

        // $this->getLogger()->info('Receive new {type} event', ['type' => $type]);
        // $this->getLogger()->info('Update: {raw}', ['raw' => $raw]);
    }
}
