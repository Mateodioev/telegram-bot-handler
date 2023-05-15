<?php

use Mateodioev\Bots\Telegram\{Api, Buttons};
use Mateodioev\Request\Request;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Context;

class Start extends MessageCommand
{
    protected string $name = 'start';
    protected string $description = 'Start the command';

    /**
     * Run command
     * @throws Exception
     */
    public function handle(Api $bot, Context $context, array $args = [])
    {
        $bot->replyTo($context->getChatId(), 'Hello world!', $context->getMessageId(), 'HTML', [
            'reply_markup' => (string) $this->getButton() // get json button
        ]);

        // log telegram context using psr logger
        $this->getLogger()->info('Received new text: {text}', ['text' => $context->message()->text()]);

        $this->getLogger()->info('Waiting 5 seconds...');
        \Amp\delay(5); // wait 5 seconds

        $bot->replyTo($context->getChatId(), '5 seconds passed!', $context->getMessageId());

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
