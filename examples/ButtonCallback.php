<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Methods\Method;
use Mateodioev\TgHandler\Commands\{CallbackCommand, StopCommand};
use Mateodioev\TgHandler\Context;

class ButtonCallback extends CallbackCommand
{
    protected string $name = 'button1';
    protected string $description = 'Button 1 callback';
    protected array $middlewares = [
        'echoPayload',
    ];

    public function handle(Api $bot, Context $context, array $args = [])
    {
        $this->getLogger()->info('Button 1 pressed');
        // log telegram context
        $this->getLogger()->info('Update: {up}', ['up' => json_encode($context->get(), JSON_PRETTY_PRINT)]);

        $payload = $context->getPayload();
        // send answerCallbackQuery method
        // if payload is empty, StopCommand will be thrown and this method will not be called
        $bot->request(Method::create([
            'callback_query_id' => $context?->callbackQuery()->id(),
            'text' => "Button 1 pressed\nPayload: " . $payload,
            'show_alert' => true
        ], 'answerCallbackQuery'));
    }
}

/**
 * @throws StopCommand Stop execution command if payload is empty
 */
function echoPayload(Context $ctx, Api $bot): void
{
    $message = 'Received new payload: "%s"';

    if (empty($ctx->getPayload())) {
        throw new StopCommand('Button payload empty');
    }

    $payload = $ctx->getPayload();
    echo sprintf($message, $payload) . PHP_EOL;
}
