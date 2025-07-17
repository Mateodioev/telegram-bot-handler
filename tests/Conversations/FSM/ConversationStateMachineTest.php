<?php

declare(strict_types=1);

namespace Tests\Conversations\FSM;

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\FSM\{
    AbstractState,
    ConversationStateMachine,
    StateTransition
};
use Mateodioev\TgHandler\Db\Memory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ConversationStateMachineTest extends TestCase
{
    private ConversationStateMachine $stateMachine;
    private NullLogger $logger;
    private Memory $db;

    protected function setUp(): void
    {
        $this->logger = new NullLogger();
        $this->db = new Memory();
        $this->stateMachine = new ConversationStateMachine('test_machine', 123, 456, $this->logger);
    }

    public function testBasicStateMachineProperties()
    {
        $this->assertEquals('test_machine', $this->stateMachine->getId());
        $this->assertEquals(123, $this->stateMachine->getUserId());
        $this->assertEquals(456, $this->stateMachine->getChatId());
        $this->assertNull($this->stateMachine->getCurrentState());
        $this->assertNull($this->stateMachine->getInitialState());
        $this->assertFalse($this->stateMachine->isComplete());
    }

    public function testAddState()
    {
        $state = $this->createTestState('test_state');
        $this->stateMachine->addState($state);

        $this->assertEquals($state, $this->stateMachine->getStateById('test_state'));
    }

    public function testSetInitialState()
    {
        $state = $this->createTestState('initial');
        $this->stateMachine->addState($state);
        $this->stateMachine->setInitialState('initial');

        $this->assertEquals($state, $this->stateMachine->getInitialState());
        $this->assertEquals($state, $this->stateMachine->getCurrentState());
    }

    public function testStateTransition()
    {
        $state1 = $this->createTestState('state1');
        $state2 = $this->createTestState('state2');

        $this->stateMachine->addState($state1);
        $this->stateMachine->addState($state2);
        $this->stateMachine->setInitialState('state1');

        $ctx = $this->createMockContext();
        $this->stateMachine->transition('state2', $ctx);

        $this->assertEquals($state2, $this->stateMachine->getCurrentState());
    }

    public function testTransitionToNonExistentState()
    {
        $state = $this->createTestState('existing');
        $this->stateMachine->addState($state);
        $this->stateMachine->setInitialState('existing');

        $ctx = $this->createMockContext();
        $result = $this->stateMachine->transition('nonexistent', $ctx);

        $this->assertNull($result);
        $this->assertEquals($state, $this->stateMachine->getCurrentState());
    }

    public function testCanTransition()
    {
        $state1 = $this->createTestState('state1');
        $state2 = $this->createTestState('state2');

        $this->stateMachine->addState($state1);
        $this->stateMachine->addState($state2);

        $ctx = $this->createMockContext();

        $this->assertTrue($this->stateMachine->canTransition('state1', $ctx));
        $this->assertTrue($this->stateMachine->canTransition('state2', $ctx));
        $this->assertFalse($this->stateMachine->canTransition('nonexistent', $ctx));
    }

    public function testIsComplete()
    {
        $normalState = $this->createTestState('normal');
        $terminalState = $this->createTestState('terminal');
        $terminalState->setTerminal(true);

        $this->stateMachine->addState($normalState);
        $this->stateMachine->addState($terminalState);

        $this->stateMachine->setInitialState('normal');
        $this->assertFalse($this->stateMachine->isComplete());

        $this->stateMachine->setState($terminalState);
        $this->assertTrue($this->stateMachine->isComplete());
    }

    public function testReset()
    {
        $initialState = $this->createTestState('initial');
        $otherState = $this->createTestState('other');

        $this->stateMachine->addState($initialState);
        $this->stateMachine->addState($otherState);
        $this->stateMachine->setInitialState('initial');

        $this->stateMachine->setState($otherState);
        $this->assertEquals($otherState, $this->stateMachine->getCurrentState());

        $this->stateMachine->reset();
        $this->assertEquals($initialState, $this->stateMachine->getCurrentState());
    }

    public function testSaveAndLoadState()
    {
        $initialState = $this->createTestState('initial');
        $currentState = $this->createTestState('current');

        $this->stateMachine->addState($initialState);
        $this->stateMachine->addState($currentState);
        $this->stateMachine->setInitialState('initial');
        $this->stateMachine->setState($currentState);

        $this->stateMachine->saveState($this->db);

        $newMachine = new ConversationStateMachine('test_machine', 123, 456, $this->logger);
        $newMachine->addState($initialState);
        $newMachine->addState($currentState);
        $newMachine->setInitialState('initial');

        $newMachine->loadState($this->db);
        $this->assertEquals($currentState->getId(), $newMachine->getCurrentState()->getId());
        $this->assertEquals($initialState->getId(), $newMachine->getInitialState()->getId());
    }

    private function createTestState(string $id): AbstractState
    {
        return new class ($id, $id) extends AbstractState {
            public function process(Context $ctx): StateTransition
            {
                return StateTransition::to($this->getId());
            }
        };
    }

    private function createMockContext(): Context
    {
        return $this->createMock(Context::class);
    }
}
