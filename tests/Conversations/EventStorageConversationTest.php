<?php

declare(strict_types=1);

namespace Tests\Conversations;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\ClosureMessageCommand;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\{Conversation};
use Mateodioev\TgHandler\Events\EventType;
use Monolog\Test\TestCase;

/**
 * Test is conversations are returned in the last for the event storage.
 */
class EventStorageConversationTest extends TestCase
{
    public static function eventStorage()
    {
        static $eventStorage;
        $eventStorage ??= new \Mateodioev\TgHandler\EventStorage();

        return $eventStorage;
    }

    private static function getExampleEvent()
    {
        return ClosureMessageCommand::new(
            name: 'test',
            fn: function (Api $bot, Context $ctx, array $args = []) {},
        );
    }

    private static function exampleConversation()
    {
        return FakeConversation::new(1, 1);
    }

    public function testConversationsAreReturnedInLast()
    {
        $storage = self::eventStorage();
        $storage->clear();

        $storage->add(self::getExampleEvent());
        $storage->add(self::getExampleEvent());
        $storage->add(self::exampleConversation());
        $storage->add(self::getExampleEvent());

        $events = $storage->resolve(EventType::message);
        $this->assertCount(4, $events);

        $lastEvent = $events[count($events) - 1];
        $this->assertInstanceOf(FakeConversation::class, $lastEvent);
        $this->assertInstanceOf(Conversation::class, $lastEvent);
    }
}
