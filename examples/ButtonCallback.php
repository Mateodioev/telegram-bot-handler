<?php

declare(strict_types=1);

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\{CallbackCommand, StopCommand};
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Middleware\Middleware;

class ButtonCallback extends CallbackCommand
{
    protected string $name = 'button1';
    protected string $description = 'Button 1 callback';
    protected array $middlewares = [
        EchoPayload::class,
    ];

    public function execute(array $args = [])
    {
        $value = $args[EchoPayload::class];
        $this->logger()->info('Middleware result: {result}', ['result' => $value]);
        $this->logger()->info('Button 1 pressed');
        // log telegram context
        $this->logger()->info('Update: {up}', [
            'up' => \json_encode($this->ctx()->getReduced(), JSON_PRETTY_PRINT)
        ]);

        $payload = $this->ctx()->getPayload();
        // send answerCallbackQuery method
        // if payload is empty, StopCommand will be thrown and this method will not be called
        $this->api()->answerCallbackQuery(
            $this->ctx()->callbackQuery()?->id(),
            [
                'text'       => "Button 1 pressed\nPayload: " . $payload,
                'show_alert' => true,
            ]
        );
    }
}

class EchoPayload extends Middleware
{
    /**
     * @throws StopCommand Stop execution command if payload is empty
     */
    public function __invoke(Context $ctx, Api $api, array $args = [])
    {
        var_dump('Old results: ', $args);
        $message = 'Received new payload: "%s"';

        if (empty($ctx->getPayload())) {
            throw new StopCommand('Button payload empty');
        }

        $payload = $ctx->getPayload();
        return \sprintf($message, $payload) . PHP_EOL;
    }
}
