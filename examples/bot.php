<?php

use Mateodioev\TgHandler\Log\{FileStream, Logger};
use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\Utils\Exceptions\RequestException;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Start.php';

$bot = new Bot($_ENV['BOT_TOKEN']);

// Log in file
$bot->setLogger(new Logger(FileStream::fromToday(__DIR__)));

$bot->setExceptionHandler(RequestException::class, function (RequestException $e, Bot $bot, Context $ctx) {
    echo 'RequestException: ' . $e->getMessage() . PHP_EOL;

    $bot->getLogger()->warning($e->getMessage());
});

$bot->on('message', Start::get())
    ->longPolling(20);
