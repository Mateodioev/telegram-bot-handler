<?php

namespace Mateodioev\TgHandler\Events;

use function str_replace, ucwords;

/**
 * Telegram update type
 */
enum EventType
{
    case message;
    case edited_message;
    case channel_post;
    case edited_channel_post;
    case inline_query;
    case chosen_inline_result;
    case callback_query;
    case shipping_query;
    case pre_checkout_query;
    case poll;
    case poll_answer;
    case my_chat_member;
    case chat_member;
    case chat_join_request;
    case none; // se supone que nunca debe pasar eso xd

    /**
     * Get event name
     */
    public function name(): string {
        return $this->name;
    }

    /**
     * Get pretty name
     */
    public function prettyName(): string
    {
        $name = str_replace('_', ' ', $this->name());
        return ucwords($name);
    }

    /**
     * Get event name
     */
    public function value(): string {
        return $this->name();
    }

    /**
     * Create new EventType, if `$type` not found throw new Exception
     */
    public static function from(string  $type): static
    {
        try {
            return self::cases()[$type];
        } catch (\Exception) {
            throw new \Exception('Invalid value');
        }
    }

    /**
     * Create new EventType, if `$type` not found return `$default` value
     */
    public static function silentFrom(string $type, $default = static::none): static
    {
        try {
            return self::from($type);
        } catch (\Exception) {
            return $default;
        }
    }
}
