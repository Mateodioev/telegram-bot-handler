<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler;

/**
 * Bot run type
 */
enum RunState
{
    case webhook;
    case longpolling;
    case none;
}
