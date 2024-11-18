<?php

namespace Tests\FakeCtx;

use Mateodioev\Bots\Telegram\Types\{Chat, Message};

class fakeContextChatType extends ctx
{
    public static function new(): static
    {
        return new self([
            'message' => new Message([
                'chat' => new Chat([
                    'type' => 'private'
                ])
            ])
        ]);
    }
}
