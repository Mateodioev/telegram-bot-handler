<?php

namespace Mateodioev\TgHandler\Events;

use Exception;

use function str_replace;
use function ucwords;

/**
 * Telegram update type
 */
enum EventType: string
{
    case message = 'message';
    case edited_message = 'edited_message';
    case channel_post = 'channel_post';
    case edited_channel_post = 'edited_channel_post';
    case inline_query = 'inline_query';
    case chosen_inline_result = 'chosen_inline_result';
    case callback_query = 'callback_query';
    case shipping_query = 'shipping_query';
    case pre_checkout_query = 'pre_checkout_query';
    case poll = 'poll';
    case poll_answer = 'poll_answer';
    case my_chat_member = 'my_chat_member';
    case chat_member = 'chat_member';
    case chat_join_request = 'chat_join_request';
    case none = ''; // se supone que nunca debe pasar eso xd
    case all = 'all';

    /**
     * Get event name
     */
    public function name(): string
    {
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
    public function value(): string
    {
        return $this->name();
    }

    /**
     * Create new EventType, if `$type` not found return `$default` value
     */
    public static function silentFrom(string $type, $default = self::none): self
    {
        try {
            return self::from($type);
        } catch (Exception) {
            return $default;
        }
    }
}
