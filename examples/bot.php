<?php

use Mateodioev\TgHandler\BotConfig;
use Mateodioev\TgHandler\Log\{BulkStream, Logger, TerminalStream, PhpNativeStream};
use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\Utils\Exceptions\RequestException;

require __DIR__ . '/bootstrap.php';

$bot = Bot::fromConfig($config);

// Exception handler for RequestException
$bot->setExceptionHandler(RequestException::class, function (RequestException $e, Bot $bot, Context $ctx) {
    echo 'RequestException: ' . $e->getMessage() . PHP_EOL;
});

$bot->onEvent(new Message)
    ->onEvent(Start::get())
    ->onEvent(ButtonCallback::get())
    ->onEvent(Params::get())
    ->onEvent(Name::get());

$bot->longPolling(20, false, $config->async());
