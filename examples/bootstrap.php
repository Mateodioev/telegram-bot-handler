<?php

use Mateodioev\TgHandler\BotConfig;
use Mateodioev\TgHandler\Log\{BulkStream, Logger, PhpNativeStream, TerminalStream};

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/All.php';
require __DIR__ . '/Start.php';
require __DIR__ . '/Params.php';
require __DIR__ . '/ButtonCallback.php';
require __DIR__ . '/Message.php';
require __DIR__ . '/Name.php';
require __DIR__ . '/nameConversation.php';
require __DIR__ . '/TestFilters.php';

// Log php error and print in terminal
$streamCollection = new BulkStream(
    new TerminalStream(),
    (new PhpNativeStream())->activate(__DIR__)
);
$logger = new Logger($streamCollection);

// Config from env vars
$config = BotConfig::fromEnv()
    ->setLogger($logger->setLevel(Logger::ALL))
    ->setAsync(true)
// ->setToken($myToken) // Set your token
;
