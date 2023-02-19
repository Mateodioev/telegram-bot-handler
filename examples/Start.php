<?php

use Mateodioev\Bots\Telegram\{Api, Buttons};
use Mateodioev\Request\Request;
use Mateodioev\TgHandler\Commands\MessageCommand;

class Start extends MessageCommand
{
	protected string $name = 'start';
    protected string $description = 'Start the command';

    /**
     * Run command
     * @throws Exception
     */
	public function handle(Api $bot, Mateodioev\TgHandler\Context $context)
	{
        $bot->replyTo($context->getChatId(), 'Hello world!', $context->getMessageId(), 'HTML', [
            'reply_markup' => (string) $this->getButton() // get json button
        ]);

        $this->getLogger()->info('Received new text: ' . $context->message()->text());

        // This will throw an exception
        Request::GET('https://invalidurl.invalid')->run();
	}

    protected function getButton(): Buttons
    {
        return Buttons::create()
            ->addCeil(['text' => 'Button 1', 'callback_data' => 'button1'])
            ->addCeil(['text' => 'Button 1 with payload', 'callback_data' => 'button1 Custom payload'])
            ->AddLine()
            ->addCeil(['text' => 'Docs', 'url' => 'https://core.telegram.org/bots/api']);
    }
}
