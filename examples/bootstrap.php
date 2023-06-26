<?php

use Mateodioev\TgHandler\BotConfig;
use Mateodioev\TgHandler\Log\{BulkStream, Logger, TerminalStream, PhpNativeStream};

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Start.php';
require __DIR__ . '/Params.php';
require __DIR__ . '/ButtonCallback.php';
require __DIR__ . '/Message.php';
require __DIR__ . '/Name.php';
require __DIR__ . '/nameConversation.php';

// Log php error and print in terminal
BulkStream::add(new TerminalStream);
BulkStream::add((new PhpNativeStream)->activate(__DIR__));

// Config from env vars
$config = BotConfig::fromEnv()
    ->setLogger(new Logger(new BulkStream))
    ->setAsync(true);
// ->setToken($myToken) // Set your token
