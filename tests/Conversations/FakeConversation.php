<?php

declare(strict_types=1);

namespace Tests\Conversations;

use Mateodioev\TgHandler\Conversations\MessageConversation;

class FakeConversation extends MessageConversation
{
    public function execute(array $args = [])
    {
    }
}
