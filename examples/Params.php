<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Context;

class Params extends MessageCommand
{
    protected string $name = 'params';
    // match string, string, number
    protected string $params = '{w:user} {w:name} {d:age}';

    /**
     * Run command
     * @throws Exception
     */
    public function handle(Api $bot, Context $context, array $args = [])
    {
        $bot->replyTo(
            $context->getChatId(),
            \sprintf(
                'User: %s | Name: %s | Age: %d',
                $this->param('user'), $this->param('name'), $this->param('age')
            ),
            $context->getMessageId()
        );
    }
}