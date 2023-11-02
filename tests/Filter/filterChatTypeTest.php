<?php

namespace Tests\Filter;

use Mateodioev\TgHandler\Filters\FilterChatType;
use PHPUnit\Framework\Attributes\{DataProvider, Depends};
use PHPUnit\Framework\TestCase;
use Tests\FakeCtx\fakeContextChatType;

class filterChatTypeTest extends TestCase
{
    public static function chatTypesProvider(): array
    {
        return [
            ['private'],
            ['group'],
            ['supergroup'],
            ['channel'],
        ];
    }


    #[DataProvider('chatTypesProvider')]
    public function testCreateFilter(string $chatType)
    {
        $filter = new FilterChatType($chatType);

        $this->assertInstanceOf(FilterChatType::class, $filter);
    }


    #[DataProvider('chatTypesProvider')]
    public function testApplyFilter(string $chatType)
    {
        $filter                   = new FilterChatType($chatType);
        $ctx                      = fakeContextChatType::new();
        $ctx->message->chat->type = $chatType;

        $this->assertTrue($filter->apply(
            $ctx
        ));
    }
}
