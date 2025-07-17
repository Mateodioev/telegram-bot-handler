<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\TgHandler\Context;

class StateTransition
{
    public function __construct(
        private readonly string $toStateId,
        private readonly ?string $condition = null,
        private $guard = null,
        private $action = null
    ) {
    }

    public function getToStateId(): string
    {
        return $this->toStateId;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function canExecute(Context $ctx): bool
    {
        if ($this->guard === null) {
            return true;
        }

        return ($this->guard)($ctx);
    }

    public function execute(Context $ctx): void
    {
        if ($this->action !== null) {
            ($this->action)($ctx);
        }
    }

    public static function to(string $stateId): self
    {
        return new self($stateId);
    }

    public static function conditionalTo(string $stateId, string $condition, ?callable $guard = null): self
    {
        return new self($stateId, $condition, $guard);
    }

    public static function actionTo(string $stateId, callable $action): self
    {
        return new self($stateId, null, null, $action);
    }
}
