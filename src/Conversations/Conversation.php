<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\TgHandler\Events\TemporaryEvent;

interface Conversation extends TemporaryEvent
{
    public const UNDEFINED_TTL = null;

    /**
     * Get the time to live (TTL) value in seconds.
     * If can run for unlimited time, return null
     */
    public function ttl(): ?int;

    /**
     * Called when the conversation is expired.
     * In this method you only have access to the context where the conversation was created.
     * Additional the bot api and the logger are available.
     */
    public function onExpired(): void;

    /**
     * Message format to validate the user answer
     */
    public function format(): string;

    /**
     * Get param from user answer
     */
    public function param(string $key, mixed $default = null): mixed;
}
