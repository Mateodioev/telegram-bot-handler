<?php

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\TgHandler\Events\EventInterface;

interface Conversation extends EventInterface
{
    /**
     * Message format to validate the user answer
     */
    public function format(): string;

    /**
     * Get param from user answer
     */
    public function param(string $key, mixed $default = null): mixed;
}