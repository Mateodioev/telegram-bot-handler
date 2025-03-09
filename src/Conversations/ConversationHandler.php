<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

abstract class ConversationHandler extends abstractEvent implements Conversation
{
    protected string $format = '{all:payload}';

    private ?Matcher $pattern = null;
    private array $params = [];

    protected ?int $ttl = Conversation::UNDEFINED_TTL;

    protected function __construct(
        public readonly int $chatId,
        public readonly int $userId,
    ) {
    }

    /**
     * @throws ConversationException
     */
    protected static function create(EventType $type, int $chatId, int $userId): static
    {
        /// You can use in webhook mode if you are using servers like amphp or swoole
        /* if (Bot::$state === RunState::webhook) {
            throw new ConversationException('Can\'t use Conversation handlers while bot is running in webhook mode');
        } */

        return (new static($chatId, $userId))
            ->setType($type);
    }

    public function isValid(): bool
    {

        $text = $this->ctx()->getMessageText() ?? '';
        $isValid = $this->type() === $this->ctx()->eventType() // validate event type
            && $this->chatId === $this->ctx()->getChatId() // validate chat id
            && $this->userId === $this->ctx()->getUserId() // validate user id
            && $this->getPattern()->isValid($text, true); // Validate pattern

        if (!$isValid) {
            return false;
        }

        $this->params = $this->getPattern()->match($text); // Get params from command
        return true;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function format(): string
    {
        return $this->format;
    }

    /**
     * Set event type
     */
    protected function setType(EventType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function ttl(): ?int
    {
        return $this->ttl;
    }

    public function onExpired(): void
    {
    }

    private function getPattern(): Matcher
    {
        if ($this->pattern instanceof Matcher) {
            return $this->pattern;
        }

        /** @var Matcher $this->pattern */
        $this->pattern = new Matcher($this->format());
        return $this->pattern;
    }
}
