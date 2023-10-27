<?php

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
