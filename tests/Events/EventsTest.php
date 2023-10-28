<?php

namespace Tests\Events;

use Mateodioev\TgHandler\Events\{abstractEvent, EventInterface};
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Commands\{ClosureMessageCommand, Command, MessageCommand};
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\MessageConversation;
use PHPUnit\Framework\TestCase;

class EventsTest extends TestCase
{
    public function testCreateAbstractEvent()
    {
        $ev = new class () extends abstractEvent {
            public function execute(array $args = [])
            {
            }
        };

        $this->assertInstanceOf(EventInterface::class, $ev);
    }

    public function testCreateCommand()
    {
        $cmd = new class () extends Command {
            public function execute(array $args = [])
            {
            }

            protected function buildRegex(): Matcher
            {
                return new Matcher('{all:example}');
            }

            protected function match(string $text): bool
            {
                return true;
            }
        };

        $this->assertInstanceOf(EventInterface::class, $cmd);
    }

    public function testCreateMessageCommand()
    {
        $msgCommand = new class () extends MessageCommand {
            protected string $name = 'example'; // /example
            public function handle(Api $bot, Context $context, array $args = [])
            {
            }
        };

        $this->assertInstanceOf(EventInterface::class, $msgCommand);

        $closureMsgCommand = ClosureMessageCommand::new('example', function (Api $bot, Context $context, array $args = []) {
        });

        $this->assertInstanceOf(EventInterface::class, $closureMsgCommand);
    }

    public function testCreateConversation()
    {
        $conversation = new class (1, 1) extends MessageConversation {
            public function __construct(int $chatId, int $userId)
            {
                parent::__construct($chatId, $userId);
            }

            public function execute(array $args = [])
            {
            }
        };

        $this->assertInstanceOf(EventInterface::class, $conversation);
    }
}
