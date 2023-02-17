<?php

use Mateodioev\Bots\Telegram\Api;
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
		$bot->sendMessage($context->getChatId(), 'Hello world!');
		$this->getLogger()->info('Received new text: ' . $context->message()->text());

        // This will throw an exception
        Request::GET('https://invalidurl.invalid')->run();
	}
}