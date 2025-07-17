<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Conversations\Conversation;
use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

abstract class FSMConversation extends abstractEvent implements Conversation
{
    protected StateMachine $stateMachine;
    protected ?Matcher $pattern = null;
    protected array $params = [];

    protected function __construct(
        protected readonly int $chatId,
        protected readonly int $userId,
        EventType $eventType
    ) {
        $this->setType($eventType);
        $this->stateMachine = $this->createStateMachine();
    }

    abstract protected function createStateMachine(): StateMachine;

    public function isValid(): bool
    {
        $currentState = $this->stateMachine->getCurrentState();

        if ($currentState === null) {
            return false;
        }

        $text = $this->ctx()->getMessageText() ?? '';
        $isValid = $this->type() === $this->ctx()->eventType()
            && $this->chatId === $this->ctx()->getChatId()
            && $this->userId === $this->ctx()->getUserId()
            && $this->getPattern($currentState)->isValid($text, true);

        if (!$isValid) {
            return false;
        }

        $this->params = $this->getPattern($currentState)->match($text);
        return true;
    }

    public function execute(array $args = []): ?Conversation
    {
        $this->stateMachine->loadState($this->db());
        $this->stateMachine->setLogger($this->logger());

        $currentState = $this->stateMachine->getCurrentState();

        if ($currentState === null) {
            $this->logger()->warning('FSM conversation has no current state');
            return null;
        }

        $transition = $currentState->process($this->ctx());

        if ($transition->canExecute($this->ctx())) {
            $transition->execute($this->ctx());
            $nextConversation = $this->stateMachine->transition($transition->getToStateId(), $this->ctx());

            $this->stateMachine->saveState($this->db());

            if ($this->stateMachine->isComplete()) {
                $this->onComplete();
                return null;
            }

            return $nextConversation;
        }

        return null;
    }

    public function ttl(): ?int
    {
        return $this->stateMachine->getCurrentState()?->getTtl();
    }

    public function onExpired(): void
    {
        $this->stateMachine->reset();
        $this->stateMachine->saveState($this->db());
    }

    public function format(): string
    {
        return $this->stateMachine->getCurrentState()?->getFormat() ?? '{all:payload}';
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    protected function setType(EventType $type): self
    {
        $this->type = $type;
        return $this;
    }

    protected function onComplete(): void
    {
    }

    private function getPattern(State $state): Matcher
    {
        if ($this->pattern === null) {
            $this->pattern = new Matcher($state->getFormat());
        }

        return $this->pattern;
    }
}
