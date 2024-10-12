<?php

declare (strict_types=1);

namespace Mateodioev\TgHandler;

/**
 * Bot run type
 */
enum RunState
{
    /**
     * The bot is running in [webhook](https://core.telegram.org/bots/api#webhookinfo) mode
     * @var RunState::webhook
     */
    case webhook;

    /**
     * The bot is running using a infinite loop
     */
    case longpolling;

    /**
     * How knows
     */
    case none;

    /**
     * The bot should stop in the next iteration
     * Only works in long polling mode
     */
    case stop;
}
