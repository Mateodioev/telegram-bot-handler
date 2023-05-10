<?php

use Mateodioev\TgHandler\Events\Types\AllEvent;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

/**
 * AllEvent receive all event types
 */
class All extends AllEvent
{
    public function execute(Api $bot, Context $context, array $args = [])
    {
        $type = $context->eventType()->prettyName();
        $raw  = \json_encode($context->get(), JSON_PRETTY_PRINT);

        $this->getLogger()->info('Receive new {type} event', compact('type'));
        $this->getLogger()->info('Update: {raw}', compact('raw'));
	}
}
