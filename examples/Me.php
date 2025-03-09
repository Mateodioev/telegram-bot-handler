<?php

declare(strict_types=1);

use Mateodioev\Bots\Telegram\Buttons\ButtonFactory;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Filters\FilterPrivateChat;

#[FilterPrivateChat]
class Me extends MessageCommand
{
    private const int ADMIN_ID = 996202950;

    protected string $name = 'me';

    /**
     * Run command
     * @throws Exception
     */
    public function execute(array $args = []): void
    {
        $user = $this->ctx()->getUser();

        $this->api()->replyToMessage(
            $this->ctx()->message(),
            sprintf(
                'Hello %s, your id is <code>%d</code> and your username is <i>%s</i>',
                $user->mention(),
                $user->id(),
                $user->username() ?? 'none'
            )
        );
    }

    public function onInvalidFilters(): ?bool
    {
        if ($this->ctx()->getUserId() === self::ADMIN_ID) {
            return true; // execute method handle
        }

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

        return false;
    }
}
