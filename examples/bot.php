<?php

use Mateodioev\TgHandler\Commands\Generics\FallbackCallbackCommand;
use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\Utils\Exceptions\RequestException;

require __DIR__ . '/bootstrap.php';

$bot = Bot::fromConfig($config);

// Exception handler for RequestException
$bot->setExceptionHandler(RequestException::class, function (RequestException $e, Bot $bot, Context $ctx) {
    $bot->getLogger()->error($e::class . ': ' . $e->getMessage());
});

$bot->onEvent(new Message())
    ->onEvent(new TestFilters())
    ->onEvent(new All())
    ->onEvent(new StickerListener())
    ->onEvent(ButtonCallback::get());

// Register text command
$bot->registerCommand(Start::get())
    ->add(Params::get())
    ->add(Name::get())
    ->add(Me::get())
    ->withDefaultFallbackCommand(); // use this to register the fallback command

// Register callback command
$bot->registerCommand(ButtonCallback::get())
    ->setFallbackCommand(new FallbackCallbackCommand());

try {
    $bot->longPolling(
        timeout: 60,
        ignoreOldUpdates: true,
        async: $config->async()
    );
} catch (Exception $e) {
    echo $e;
}
