<?php

namespace Tests\Db;

use Mateodioev\TgHandler\Db\Memory;
use PHPUnit\Framework\TestCase;
use stdClass;

class MemoryTest extends TestCase
{
    protected static Memory $db;

    public static function providerDb(): array
    {
        return [
            ['key1', 'value1'],
            ['key2', 100],
            ['key3', 100.111],
            ['key4', false],
            ['key5', new stdClass()],
        ];
    }

    public static function setUpBeforeClass(): void
    {
        self::$db = new Memory();
    }

    /**
     * @dataProvider providerDb
     */
    public function testSaveValues(string $key, mixed $value)
    {
        $this->assertTrue(self::$db->save($key, $value));
    }

    /**
     * @dataProvider providerDb
     */
    public function testCheckIfExists(string $key)
    {
        $this->assertTrue(self::$db->exists($key));
    }

    /**
     * @dataProvider providerDb
     */
    public function testAssertGetValue(string $key, mixed $value)
    {
        $this->assertEquals($value, self::$db->get($key));
    }

    /**
     * @dataProvider providerDb
     */
    public function testDeleteValue(string $key)
    {
        $this->assertTrue(self::$db->delete($key));
    }
}
