<?php

use Mateodioev\TgHandler\Log\{BulkStream, Logger, TerminalStream, PhpNativeStream};
use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\Utils\Exceptions\RequestException;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Start.php';
require __DIR__ . '/ButtonCallback.php';
require __DIR__ . '/Message.php';
require __DIR__ . '/All.php';

$bot = new Bot($_ENV['BOT_TOKEN']); // put your bot token here

// Log php error and print in terminal
BulkStream::add(new TerminalStream);
BulkStream::add((new PhpNativeStream)->activate(__DIR__));
$bot->setLogger(new Logger(new BulkStream));

// Exception handler for RequestException
$bot->setExceptionHandler(RequestException::class, function (RequestException $e, Bot $bot, Context $ctx) {
    echo 'RequestException: ' . $e->getMessage() . PHP_EOL;
});

$bot->onEvent(new All);
$bot->onEvent(new Message);
$bot->onEvent(Start::get());
$bot->onEvent(ButtonCallback::get());
$bot->longPolling(20, false, true);
