<?php

declare (strict_types=1);

use Mateodioev\TgHandler\Commands\DynamicMessageCommand;

/**
 * This command respond to a input like: /secretANY_STRING_HERE
 */
class DynamicStart extends DynamicMessageCommand
{
    protected string $name = 'secret';
    protected string $description = 'Command with a secret token';

    public function execute(array $args = [])
    {
        $token = $this->commandToken();
        $this->api()->replyToMessage($this->ctx()->message(), "Token: $token");
    }
}
