<?php

use Mateodioev\Bots\Telegram\Api;
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
        $this->logger()->info('Button 1 pressed');
        // log telegram context
        $this->logger()->info('Update: {up}', [
            'up' => \json_encode($context->getReduced(), JSON_PRETTY_PRINT)
        ]);

        $payload = $context->getPayload();
        // send answerCallbackQuery method
        // if payload is empty, StopCommand will be thrown and this method will not be called
        $bot->answerCallbackQuery(
            $context?->callbackQuery()->id(),
            "Button 1 pressed\nPayload: " . $payload,
            ['show_alert' => true]
        );
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