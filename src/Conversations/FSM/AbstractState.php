<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\Conversation;

abstract class AbstractState implements State
{
    protected array $transitions = [];
    protected bool $isTerminal = false;
    protected ?int $ttl = null;
    protected ?Conversation $conversation = null;

    public function __construct(
        protected readonly string $id,
        protected readonly string $name,
        protected readonly string $description = '',
        protected readonly string $format = '{all:payload}'
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function canEnter(Context $ctx): bool
    {
        return true;
    }

    public function onEnter(Context $ctx): void
    {
    }

    abstract public function process(Context $ctx): StateTransition;

    public function onExit(Context $ctx): void
    {
    }

    public function getTransitions(): array
    {
        return $this->transitions;
    }

    public function addTransition(StateTransition $transition): void
    {
        $this->transitions[] = $transition;
    }

    public function isTerminal(): bool
    {
        return $this->isTerminal;
    }

    public function setTerminal(bool $terminal): self
    {
        $this->isTerminal = $terminal;
        return $this;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTtl(?int $ttl): self
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): void
    {
        $this->conversation = $conversation;
    }
}
