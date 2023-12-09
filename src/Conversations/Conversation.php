<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\TgHandler\Events\TemporaryEvent;

interface Conversation extends TemporaryEvent
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
