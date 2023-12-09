<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Buttons\ButtonFactory;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Filters\FilterPrivateChat;

#[FilterPrivateChat]
class Me extends MessageCommand
{
    protected string $name = 'me';

    /**
     * Run command
     * @throws Exception
     */
    public function handle(Api $bot, Context $context, array $args = [])
    {
        $user = $context->getUser();

        $bot->replyToMessage(
            $context->message(),
            \sprintf(
                'Hello %s, your id is <code>%d</code> and your username is <i>%s</i>',
                $user->mention(),
                $user->id(),
                $user->username() ?? 'none'
            )
        );
    }

    public function onInvalidFilters(): bool
    {
        $this->api()->replyToMessage(
            $this->ctx()->message(),
            'This command only works in private chat',
            params: [
                'reply_markup' => (string) ButtonFactory::inlineKeyboardMarkup()->addCeil([
                    'text' => 'Test this invalid callback',
                    'callback_data' => 'test invalid callback'
                ])
            ]
        );
        return true;
    }
}
