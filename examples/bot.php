<?php

use Mateodioev\TgHandler\Log\{FileStream, Logger};

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Start.php';

\Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

$bot = new \Mateodioev\TgHandler\Bot($_ENV['BOT_TOKEN']);

// Log in file
$bot->setLogger(new Logger(FileStream::fromToday(__DIR__)));

$bot->on('message', Start::get())
    ->longPolling(20);
