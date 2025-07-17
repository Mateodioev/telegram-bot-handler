<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\Conversation;
use Mateodioev\TgHandler\Db\DbInterface;
use Psr\Log\LoggerInterface;

interface StateMachine
{
    public function getCurrentState(): ?State;

    public function setState(State $state): void;

    public function getStateById(string $stateId): ?State;

    public function transition(string $stateId, Context $ctx): ?Conversation;

    public function canTransition(string $stateId, Context $ctx): bool;

    public function addState(State $state): void;

    public function setInitialState(string $stateId): void;

    public function getInitialState(): ?State;

    public function isComplete(): bool;

    public function reset(): void;

    public function getId(): string;

    public function getUserId(): int;

    public function getChatId(): int;

    public function saveState(DbInterface $db): void;

    public function loadState(DbInterface $db): void;

    public function setLogger(LoggerInterface $logger): void;
}
