<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Methods\Method;
use Mateodioev\TgHandler\Commands\CallbackCommand;
use Mateodioev\TgHandler\Context;

class ButtonCallback extends CallbackCommand
{
    protected string $name = 'button1';
    protected string $description = 'Button 1 callback';

    public function handle(Api $bot, Context $context)
    {
        $this->getLogger()->info('Button 1 pressed');

        // log telegram context
        $this->getLogger()->info('Update: {up}', ['up' => json_encode($context->get(), JSON_PRETTY_PRINT)]);

        $payload = $context->getPayload();
        // send answerCallbackQuery method
        $bot->request(Method::create([
            'callback_query_id' => $context?->callbackQuery()->id(),
            'text' => "Button 1 pressed\nPayload: " . $payload,
            'show_alert' => true
        ], 'answerCallbackQuery'));
    }
}