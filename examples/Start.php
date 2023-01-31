<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\MessageCommand;

class Start extends MessageCommand
{
	protected string $name = 'start';
    protected string $description = 'Start the command';

	/**
	 * Run command
	 */
	public function handle(Api $bot, Mateodioev\TgHandler\Context $context)
	{
		$bot->sendMessage($context->getChatId(), 'Hello world!');

		$this->getLogger()->info('Received new text: ' . $context->message()->text());
	}
}