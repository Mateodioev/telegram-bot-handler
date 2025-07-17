<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\Conversation;
use Mateodioev\TgHandler\Db\DbInterface;
use Psr\Log\{LoggerInterface, NullLogger};

class ConversationStateMachine implements StateMachine
{
    private array $states = [];
    private ?State $currentState = null;
    private ?State $initialState = null;
    private LoggerInterface $logger;

    public function __construct(
        private readonly string $id,
        private readonly int $userId,
        private readonly int $chatId,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function getCurrentState(): ?State
    {
        return $this->currentState;
    }

    public function setState(State $state): void
    {
        $this->currentState = $state;
        $this->logger->debug('FSM state changed to {state}', ['state' => $state->getId()]);
    }

    public function getStateById(string $stateId): ?State
    {
        return $this->states[$stateId] ?? null;
    }

    public function transition(string $stateId, Context $ctx): ?Conversation
    {
        if (!$this->canTransition($stateId, $ctx)) {
            $this->logger->warning('FSM transition to {state} not allowed', ['state' => $stateId]);
            return null;
        }

        $fromState = $this->currentState;
        $toState = $this->getStateById($stateId);

        if ($toState === null) {
            $this->logger->error('FSM state {state} not found', ['state' => $stateId]);
            return null;
        }

        if ($fromState !== null) {
            $fromState->onExit($ctx);
        }

        $this->setState($toState);
        $toState->onEnter($ctx);

        return $toState->getConversation();
    }

    public function canTransition(string $stateId, Context $ctx): bool
    {
        $state = $this->getStateById($stateId);

        if ($state === null) {
            return false;
        }

        return $state->canEnter($ctx);
    }

    public function addState(State $state): void
    {
        $this->states[$state->getId()] = $state;
        $this->logger->debug('FSM state {state} added', ['state' => $state->getId()]);
    }

    public function setInitialState(string $stateId): void
    {
        $state = $this->getStateById($stateId);
        if ($state !== null) {
            $this->initialState = $state;
            if ($this->currentState === null) {
                $this->currentState = $state;
            }
        }
    }

    public function getInitialState(): ?State
    {
        return $this->initialState;
    }

    public function isComplete(): bool
    {
        return $this->currentState?->isTerminal() ?? false;
    }

    public function reset(): void
    {
        $this->currentState = $this->initialState;
        $this->logger->debug('FSM reset to initial state');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function saveState(DbInterface $db): void
    {
        $stateData = [
            'current_state' => $this->currentState?->getId(),
            'initial_state' => $this->initialState?->getId(),
        ];

        $db->save($this->getStateKey(), json_encode($stateData));
        $this->logger->debug('FSM state saved', ['state' => $this->currentState?->getId()]);
    }

    public function loadState(DbInterface $db): void
    {
        $stateData = $db->get($this->getStateKey());

        if ($stateData === null) {
            return;
        }

        $data = json_decode($stateData, true);

        if ($data['current_state'] && isset($this->states[$data['current_state']])) {
            $this->currentState = $this->states[$data['current_state']];
        }

        if ($data['initial_state'] && isset($this->states[$data['initial_state']])) {
            $this->initialState = $this->states[$data['initial_state']];
        }

        $this->logger->debug('FSM state loaded', ['state' => $this->currentState?->getId()]);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function getStateKey(): string
    {
        return sprintf('fsm_%s_%d_%d', $this->id, $this->userId, $this->chatId);
    }
}
