<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler;

use Closure;
use Mateodioev\Bots\Telegram\Interfaces\TypesInterface;
use Mateodioev\Bots\Telegram\Types\{
    Document,
    Update,
    User
};
use Mateodioev\TgHandler\Events\EventType;

use function explode;
use function strlen;
use function substr;
use function trim;

/**
 * Telegram context
 */
class Context extends Update
{
    private ?EventType $type = null;
    private array $cache = [];

    public static function fromUpdate(Update $update): Context
    {
        return new self($update->get());
    }

    public function getUser(): ?User
    {
        return $this->resolve('user', function (): ?User {
            return $this->message()?->from()
                ?? $this->callbackQuery()?->from()
                ?? $this->inlineQuery()?->from()
                ?? null;
        });
    }

    public function getUserId(): ?int
    {
        return $this->getUser()?->id();
    }

    public function getUserName(): ?string
    {
        return $this->getUser()?->username();
    }

    public function getChatId(): ?int
    {
        return $this->resolve('chat_id', function (): ?int {
            return $this->message()?->chat()?->id()
                ?? $this->callbackQuery()?->message()?->chat()?->id()
                ?? null;
        });
    }

    public function getMessageId(): ?int
    {
        return $this->resolve('message_id', function (): ?int {
            return $this->message()?->messageId()
                ?? $this->callbackQuery()?->message()?->messageId()
                ?? null;
        });
    }

    public function getMessageText(): ?string
    {
        return $this->resolve('message_text', function (): ?string {
            return $this->message()?->text()
                ?? $this->callbackQuery()?->data()
                ?? $this->inlineQuery()?->query()
                ?? null;
        });
    }

    public function getPayload(): string
    {
        return $this->resolve('message_payload', function (): string {
            $text = $this->getMessageText() ?? '';
            $command = explode(' ', $text)[0] ?? '';
            return substr($text, strlen($command) + 1);
        });
    }

    public function getChatType(): ?string
    {
        return $this->resolve('chat_type', function (): ?string {
            return $this->message()?->chat()?->type()
                ?? $this->callbackQuery()?->message()?->chat()?->type()
                ?? null;
        });
    }

    public function getFullName(): string
    {
        return $this->resolve('user_full_name', function (): string {
            return trim($this->getFirsName() . ' ' . $this->getLastName());
        });
    }

    public function getFirsName(): ?string
    {
        return $this->resolve('first_name', function (): ?string {
            return $this->getUser()?->firstName()
                ?? $this->message()?->replyToMessage()?->from()?->firstName()
                ?? null;
        });
    }

    public function getLastName(): ?string
    {
        return $this->resolve('last_name', function (): ?string {
            return $this->getUser()?->lastName()
                ?? $this->message()?->replyToMessage()?->from()?->lastName()
                ?? null;
        });
    }

    public function getDocument(): ?Document
    {
        return $this->resolve('document', function (): ?Document {
            return $this->message()?->document()
                ?? $this->message()?->replyToMessage()?->document()
                ?? null;
        });
    }

    /**
     * Get event type
     */
    public function eventType(): EventType
    {
        if ($this->type instanceof EventType) {
            return $this->type;
        }

        $eventTypes = $this->properties();
        unset($eventTypes['update_id']);

        foreach ($eventTypes as $type => $value) {
            if ($value instanceof TypesInterface) {
                $this->type = EventType::silentFrom($type);
                return $this->type;
            }
        }

        $this->type = EventType::none;
        return $this->type;
    }

    /**
     * Resolve a value
     *
     * @template T
     *
     * @param string $key
     * @param \Closure(...): T $callback
     *
     * @return T
     */
    private function resolve(string $key, Closure $callback)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $this->cache[$key] = $callback();
        return $this->cache[$key];
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
