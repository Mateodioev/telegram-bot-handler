<?php

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Events\{abstractEvent, EventType};
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

abstract class ConversationHandler extends abstractEvent implements Conversation
{
    protected string $format = '{all:payload}';

    private ?Matcher $pattern = null;
    private array $params = [];

    protected function __construct(
        private int $chatId,
        private int $userId,
    ) {
    }

    protected static function create(EventType $type, int $chatId, int $userId): static
    {
        return (new static($chatId, $userId))
            ->setType($type);
    }

    public function isValid(Api $bot, Context $context): bool
    {
        $text = $context->getMessageText() ?? '';
        $isValid = 1 === 1
            && $this->chatId === $context->getChatId()
            && $this->userId === $context->getUserId()
            && $this->type() === $context->eventType()
            && $this->getPattern()->isValid($text, true);

        if ($isValid)
            $this->params = $this->getPattern()->match($text);

        return $isValid;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function format(): string
    {
        return $this->format;
    }

    abstract public function execute(Api $bot, Context $context, array $args = []);

    /**
     * Set event type
     */
    protected function setType(EventType $type): static
    {
        $this->type = $type;
        return $this;
    }

    private function getPattern(): Matcher
    {
        if ($this->pattern instanceof Matcher)
            return $this->pattern;

        $this->pattern = new Matcher($this->format());
        return $this->pattern;
    }
}