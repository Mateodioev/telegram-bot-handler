<?php

declare(strict_types=1);

use Mateodioev\TgHandler\BotConfig;
use Mateodioev\TgHandler\Log\{BulkStream, Logger, PhpNativeStream, TerminalStream};
use SimpleLogger\Formatters\DefaultConsoleFormatter;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/All.php';
require __DIR__ . '/StickerListener.php';
require __DIR__ . '/TemporaryConversation.php';
require __DIR__ . '/Start.php';
require __DIR__ . '/DynamicStart.php';
require __DIR__ . '/Params.php';
require __DIR__ . '/Me.php';
require __DIR__ . '/GetUsage.php';
require __DIR__ . '/ButtonCallback.php';
require __DIR__ . '/Message.php';
require __DIR__ . '/Name.php';
require __DIR__ . '/NameConversation.php';
require __DIR__ . '/TestFilters.php';

// Log php error and print in terminal
$streamCollection = new BulkStream(
    new TerminalStream(),
    (new PhpNativeStream(formatter: new DefaultConsoleFormatter()))
        ->activate(__DIR__)
);
$logger = new Logger($streamCollection);

// Config from env vars
$config = BotConfig::fromEnv()
    ->setLogger($logger->setLevel(Logger::ALL))
    ->setAsync(true)
    // ->setToken($myToken) // Set your token
;
