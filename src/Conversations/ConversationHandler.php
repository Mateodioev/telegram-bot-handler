<?php

namespace Mateodioev\TgHandler\Conversations;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Events\{abstractEvent, EventType};
use Mateodioev\TgHandler\{Bot, RunState};

abstract class ConversationHandler extends abstractEvent implements Conversation
{
    protected string $format = '{all:payload}';

    private ?Matcher $pattern = null;
    private array $params = [];

    protected function __construct(
        private readonly int $chatId,
        private readonly int $userId,
    ) {
    }

    /**
     * @throws ConversationException
     */
    protected static function create(EventType $type, int $chatId, int $userId): static
    {
        if (Bot::$state === RunState::webhook)
            throw new ConversationException('Can\'t use Conversation handlers while bot is running in webhook mode');

        return (new static($chatId, $userId))
            ->setType($type);
    }

    public function isValid(): bool
    {

        $text = $this->ctx()->getMessageText() ?? '';
        $isValid = 1 === 1
            && $this->type() === $this->ctx()->eventType() // validate event type
            && $this->chatId === $this->ctx()->getChatId() // validate chat id
            && $this->userId === $this->ctx()->getUserId() // validate user id
            && $this->getPattern()->isValid($text, true); // Validate pattern

        if (!$isValid)
            return false;

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

    private function getPattern(): Matcher
    {
        if ($this->pattern instanceof Matcher)
            return $this->pattern;

        $this->pattern = new Matcher($this->format());
        return $this->pattern;
    }

    abstract public function execute(array $args = []);
}
