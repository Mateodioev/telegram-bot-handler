<?php

declare(strict_types=1);

namespace Tests\Conversations\FSM;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Types\{Update};
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\FSM\{
    AbstractState,
    ConversationStateMachine,
    FSMConversation,
    StateMachine,
    StateTransition
};
use Mateodioev\TgHandler\Db\Memory;
use Mateodioev\TgHandler\Events\EventType;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class FSMConversationTest extends TestCase
{
    private TestFSMConversation $conversation;
    private Memory $db;
    private NullLogger $logger;

    protected function setUp(): void
    {
        $this->db = new Memory();
        $this->logger = new NullLogger();
        $this->conversation = new TestFSMConversation(123, 456);
        $this->conversation->setDb($this->db);
        $this->conversation->setLogger($this->logger);
    }

    public function testConversationInitialization()
    {
        $this->assertEquals(123, $this->conversation->getChatId());
        $this->assertEquals(456, $this->conversation->getUserId());
        $this->assertInstanceOf(StateMachine::class, $this->conversation->getStateMachine());
    }

    public function testIsValidWithCorrectInput()
    {
        $ctx = $this->createContextWithMessage('Hello world');
        $this->conversation->setVars($this->createMock(Api::class), $ctx);

        $this->assertTrue($this->conversation->isValid());
        $this->assertEquals('Hello world', $this->conversation->param('payload'));
    }

    public function testIsValidWithIncorrectEventType()
    {
        $ctx = $this->createContextWithEventType(EventType::callback_query);
        $this->conversation->setVars($this->createMock(Api::class), $ctx);

        $this->assertFalse($this->conversation->isValid());
    }

    public function testIsValidWithIncorrectChatId()
    {
        $ctx = $this->createContextWithChatId(999);
        $this->conversation->setVars($this->createMock(Api::class), $ctx);

        $this->assertFalse($this->conversation->isValid());
    }

    public function testIsValidWithIncorrectUserId()
    {
        $ctx = $this->createContextWithUserId(999);
        $this->conversation->setVars($this->createMock(Api::class), $ctx);

        $this->assertFalse($this->conversation->isValid());
    }

    public function testExecuteWithValidTransition()
    {
        $ctx = $this->createContextWithMessage('test message');
        $this->conversation->setVars($this->createMock(Api::class), $ctx);

        $result = $this->conversation->execute();

        $this->assertNull($result);
        $this->assertEquals('processed', $this->conversation->getStateMachine()->getCurrentState()->getId());
    }

    public function testExecuteWithCompletedStateMachine()
    {
        $ctx = $this->createContextWithMessage('complete');
        $this->conversation->setVars($this->createMock(Api::class), $ctx);

        $result = $this->conversation->execute();

        $this->assertNull($result);
        $this->assertTrue($this->conversation->getStateMachine()->isComplete());
    }

    public function testTtlFromCurrentState()
    {
        $this->assertEquals(3600, $this->conversation->ttl());
    }

    public function testFormatFromCurrentState()
    {
        $this->assertEquals('{all:payload}', $this->conversation->format());
    }

    public function testOnExpired()
    {
        $this->conversation->onExpired();
        $this->assertEquals('initial', $this->conversation->getStateMachine()->getCurrentState()->getId());
    }

    private function createContextWithMessage(string $message): Context
    {
        $update = new Update([
            'update_id' => 1,
            'message' => [
                'message_id' => 1,
                'date' => time(),
                'text' => $message,
                'chat' => ['id' => 123, 'type' => 'private'],
                'from' => ['id' => 456, 'is_bot' => false, 'first_name' => 'Test']
            ]
        ]);

        return Context::fromUpdate($update);
    }

    private function createContextWithEventType(EventType $eventType): Context
    {
        $update = new Update([
            'update_id' => 1,
            'callback_query' => [
                'id' => 'test',
                'data' => 'test',
                'chat_instance' => 'test_instance',
                'from' => ['id' => 456, 'is_bot' => false, 'first_name' => 'Test'],
                'message' => [
                    'message_id' => 1,
                    'date' => time(),
                    'text' => 'test',
                    'chat' => ['id' => 123, 'type' => 'private'],
                    'from' => ['id' => 456, 'is_bot' => false, 'first_name' => 'Test']
                ]
            ]
        ]);
        
        return Context::fromUpdate($update);
    }

    private function createContextWithChatId(int $chatId): Context
    {
        $update = new Update([
            'update_id' => 1,
            'message' => [
                'message_id' => 1,
                'date' => time(),
                'text' => 'test',
                'chat' => ['id' => $chatId, 'type' => 'private'],
                'from' => ['id' => 456, 'is_bot' => false, 'first_name' => 'Test']
            ]
        ]);

        return Context::fromUpdate($update);
    }

    private function createContextWithUserId(int $userId): Context
    {
        $update = new Update([
            'update_id' => 1,
            'message' => [
                'message_id' => 1,
                'date' => time(),
                'text' => 'test',
                'chat' => ['id' => 123, 'type' => 'private'],
                'from' => ['id' => $userId, 'is_bot' => false, 'first_name' => 'Test']
            ]
        ]);

        return Context::fromUpdate($update);
    }
}

class TestFSMConversation extends FSMConversation
{
    private StateMachine $testStateMachine;

    public function __construct(int $chatId, int $userId)
    {
        parent::__construct($chatId, $userId, EventType::message);
    }

    protected function createStateMachine(): StateMachine
    {
        $machine = new ConversationStateMachine('test', $this->userId, $this->chatId);

        $initialState = new class ('initial', 'Initial state') extends AbstractState {
            public function __construct(string $id, string $name)
            {
                parent::__construct($id, $name, 'Initial state', '{all:payload}');
                $this->setTtl(3600);
            }

            public function process(Context $ctx): StateTransition
            {
                $message = $ctx->getMessageText();

                if ($message === 'complete') {
                    return StateTransition::to('terminal');
                }

                return StateTransition::to('processed');
            }
        };

        $processedState = new class ('processed', 'Processed state') extends AbstractState {
            public function process(Context $ctx): StateTransition
            {
                return StateTransition::to('processed');
            }
        };

        $terminalState = new class ('terminal', 'Terminal state') extends AbstractState {
            public function __construct(string $id, string $name)
            {
                parent::__construct($id, $name);
                $this->setTerminal(true);
            }

            public function process(Context $ctx): StateTransition
            {
                return StateTransition::to('terminal');
            }
        };

        $machine->addState($initialState);
        $machine->addState($processedState);
        $machine->addState($terminalState);
        $machine->setInitialState('initial');

        $this->testStateMachine = $machine;
        return $machine;
    }

    public function getStateMachine(): StateMachine
    {
        return $this->testStateMachine;
    }
    
    public function getChatId(): int
    {
        return $this->chatId;
    }
    
    public function getUserId(): int
    {
        return $this->userId;
    }
}
