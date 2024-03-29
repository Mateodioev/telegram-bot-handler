<?php

use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\Utils\Exceptions\RequestException;

require __DIR__ . '/bootstrap.php';

$bot = Bot::fromConfig($config);

// Exception handler for RequestException
$bot->setExceptionHandler(RequestException::class, function (RequestException $e, Bot $bot, Context $ctx) {
    $bot->getLogger()->error($e::class . ': ' . $e->getMessage());
});

$bot->onEvent(new Message)
    ->onEvent(new TestFilters)
    ->onEvent(new All)
    ->onEvent(Start::get())
    ->onEvent(ButtonCallback::get())
    ->onEvent(Params::get())
    ->onEvent(Name::get());

try {
    $bot->longPolling(
        timeout: 60,
        ignoreOldUpdates: false,
        async: $config->async()
    );
} catch (Exception $e) {
    echo $e;
}
