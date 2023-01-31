<?php

namespace Mateodioev\TgHandler\Log;

/**
 * Save messages with method push
 */
interface Stream
{
    public function push(string $message): void;
}