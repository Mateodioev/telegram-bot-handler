<?php

namespace Tests\Events;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\ClosureMessageCommand;
use Mateodioev\TgHandler\Events\EventInterface;
use Mateodioev\TgHandler\{Context, EventStorage};
use PHPUnit\Framework\TestCase;

class EventStorageTest extends TestCase
{
    public static function eventStorage(): EventStorage
    {
        static $eventStorage;
        $eventStorage ??= new EventStorage();

        return $eventStorage;
    }

    /**
     * Get new event
     */
    public static function getExampleEvent(): EventInterface
    {
        return ClosureMessageCommand::new(
            name: 'test',
            fn: function (Api $bot, Context $ctx, array $args = []) {},
        );
    }

    /**
     * Get example event
     */
    public static function exampleEvent(): EventInterface
    {
        static $exampleEvent;
        $exampleEvent ??= self::getExampleEvent();

        return $exampleEvent;
    }

    public function testAddEvent()
    {
        $event = self::getExampleEvent();

        $this->assertEquals(0, self::eventStorage()->total());
        $this->assertEquals(0, self::eventStorage()->total($event->type()));

        $eventId = self::eventStorage()->add($event);
        $this->assertIsInt($eventId);
        $this->assertSame($event, self::eventStorage()->get($eventId));

        $this->assertEquals(1, self::eventStorage()->total());
        $this->assertEquals(1, self::eventStorage()->total($event->type()));

        self::eventStorage()->clear();
    }

    public function testDeleteEvent()
    {
        $event = self::getExampleEvent();

        $eventId = self::eventStorage()->add($event);

        $this->assertEquals(1, self::eventStorage()->total());
        $this->assertSame($event, self::eventStorage()->get($eventId));

        $this->assertTrue(self::eventStorage()->delete($event));
        $this->assertNull(self::eventStorage()->get($eventId));
        $this->assertEmpty(self::eventStorage()->resolve($event->type()));
    }

    public function testClearEvents()
    {
        $event = self::getExampleEvent();

        $eventId = self::eventStorage()->add($event);
        $this->assertIsInt($eventId);
        $this->assertSame($event, self::eventStorage()->get($eventId));

        $this->assertEquals(1, self::eventStorage()->total());
        $this->assertEquals(1, self::eventStorage()->total($event->type()));

        self::eventStorage()->clear();

        $this->assertEquals(0, self::eventStorage()->total());
        $this->assertEquals(0, self::eventStorage()->total($event->type()));
    }
}
