<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Buttons\{ButtonFactory, InlineKeyboardMarkupFactory};
use Mateodioev\Bots\Telegram\Types\InlineKeyboardButton;
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
        $bot->replyTo($context->getChatId(), 'Hello world!', $context->getMessageId(), params: [
            'reply_markup' => (string) $this->getButton()  // get json button
        ]);

        // log telegram context using psr/log
        $this->logger()->info('Received new text: {text}', ['text' => $context->message()->text()]);

        // Get payload from command
        $this->logger()->info('Payload: "{payload}"', ['payload' => $this->param('payload', 'Not payload')]);

        $message = $bot->sendMessage($context->getChatId(), 'Please wait 5 seconds...');
        $this->sleep(5); // wait 5 seconds
        $result = $bot->editMessageText($message->chat()->id(), '5 seconds passed!', ['message_id' => $message->messageId()]);

        $this->logger()->info('Result: {res}', ['res' => json_encode($result->getReduced())]);

        // This will throw an exception
        Request::GET('https://invalidurl.invalid')->run();
    }

    protected function getButton(): InlineKeyboardMarkupFactory
    {
        return ButtonFactory::inlineKeyboardMarkup()
            ->addCeil(new InlineKeyboardButton(['text' => 'Button 1', 'callback_data' => 'button1']))
            ->addCeil(new InlineKeyboardButton(['text' => 'Button 1 with payload', 'callback_data' => 'button1 Custom payload']))
            ->addLine()
            ->addCeil(new InlineKeyboardButton(['text' => 'Docs', 'url' => 'https://core.telegram.org/bots/api']));
    }
}
