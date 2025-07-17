<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\Conversation;

interface State
{
    public function getId(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function getFormat(): string;

    public function canEnter(Context $ctx): bool;

    public function onEnter(Context $ctx): void;

    public function process(Context $ctx): StateTransition;

    public function onExit(Context $ctx): void;

    public function getTransitions(): array;

    public function addTransition(StateTransition $transition): void;

    public function isTerminal(): bool;

    public function getTtl(): ?int;

    public function getConversation(): ?Conversation;

    public function setConversation(Conversation $conversation): void;
}
