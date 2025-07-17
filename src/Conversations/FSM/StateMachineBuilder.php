<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Psr\Log\LoggerInterface;
use RuntimeException;

class StateMachineBuilder
{
    private ConversationStateMachine $stateMachine;
    private array $states = [];

    public function __construct(
        string $id,
        int $userId,
        int $chatId,
        ?LoggerInterface $logger = null
    ) {
        $this->stateMachine = new ConversationStateMachine($id, $userId, $chatId, $logger);
    }

    public function addState(string $id, string $name, string $description = '', string $format = '{all:payload}'): StateBuilder
    {
        $state = new class ($id, $name, $description, $format) extends AbstractState {
            private $processor;

            public function setProcessor(callable $processor): void
            {
                $this->processor = $processor;
            }

            public function process(\Mateodioev\TgHandler\Context $ctx): StateTransition
            {
                if ($this->processor === null) {
                    throw new RuntimeException("No processor defined for state {$this->getId()}");
                }

                return ($this->processor)($ctx, $this->getConversation());
            }
        };

        $this->states[$id] = $state;
        $this->stateMachine->addState($state);

        return new StateBuilder($state);
    }

    public function setInitialState(string $stateId): self
    {
        $this->stateMachine->setInitialState($stateId);
        return $this;
    }

    public function build(): ConversationStateMachine
    {
        return $this->stateMachine;
    }

    public function getState(string $id): ?AbstractState
    {
        return $this->states[$id] ?? null;
    }
}

class StateBuilder
{
    public function __construct(private readonly AbstractState $state)
    {
    }

    public function withProcessor(callable $processor): self
    {
        $this->state->setProcessor($processor);
        return $this;
    }

    public function onEnter(callable $callback): self
    {
        // Implementation can be added later if needed
        return $this;
    }

    public function onExit(callable $callback): self
    {
        // Implementation can be added later if needed
        return $this;
    }

    public function canEnter(callable $guard): self
    {
        // Implementation can be added later if needed
        return $this;
    }

    public function terminal(bool $terminal = true): self
    {
        $this->state->setTerminal($terminal);
        return $this;
    }

    public function ttl(int $ttl): self
    {
        $this->state->setTtl($ttl);
        return $this;
    }

    public function getState(): AbstractState
    {
        return $this->state;
    }
}
