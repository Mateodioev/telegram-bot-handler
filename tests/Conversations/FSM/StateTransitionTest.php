<?php

declare(strict_types=1);

namespace Tests\Conversations\FSM;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\FSM\StateTransition;
use PHPUnit\Framework\TestCase;

class StateTransitionTest extends TestCase
{
    public function testBasicTransition()
    {
        $transition = StateTransition::to('target_state');

        $this->assertEquals('target_state', $transition->getToStateId());
        $this->assertNull($transition->getCondition());

        $ctx = $this->createMock(Context::class);
        $this->assertTrue($transition->canExecute($ctx));

        $transition->execute($ctx);
    }

    public function testConditionalTransition()
    {
        $guard = function (Context $ctx) {
            return $ctx->getMessageText() === 'valid';
        };

        $transition = StateTransition::conditionalTo('target_state', 'condition', $guard);

        $this->assertEquals('target_state', $transition->getToStateId());
        $this->assertEquals('condition', $transition->getCondition());

        $ctx = $this->createMock(Context::class);
        $ctx->method('getMessageText')->willReturn('valid');
        $this->assertTrue($transition->canExecute($ctx));

        $ctx2 = $this->createMock(Context::class);
        $ctx2->method('getMessageText')->willReturn('invalid');
        $this->assertFalse($transition->canExecute($ctx2));
    }

    public function testActionTransition()
    {
        $actionExecuted = false;
        $action = function (Context $ctx) use (&$actionExecuted) {
            $actionExecuted = true;
        };

        $transition = StateTransition::actionTo('target_state', $action);

        $this->assertEquals('target_state', $transition->getToStateId());
        $this->assertNull($transition->getCondition());

        $ctx = $this->createMock(Context::class);
        $this->assertTrue($transition->canExecute($ctx));

        $transition->execute($ctx);
        $this->assertTrue($actionExecuted);
    }

    public function testTransitionWithNoGuard()
    {
        $transition = new StateTransition('target_state');

        $ctx = $this->createMock(Context::class);
        $this->assertTrue($transition->canExecute($ctx));
    }
}
