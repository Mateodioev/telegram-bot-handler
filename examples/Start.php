<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\MessageCommand;

class Start extends MessageCommand
{
	protected string $name = 'start';
    protected string $description = '';

	/**
	 * Run command
	 */
	public function handle(Api $bot, Mateodioev\TgHandler\Context $context)
	{
		$bot->sendMessage($context->message()->chat()->id(), 'Hello world!');
		echo 'Done!';
	}
}