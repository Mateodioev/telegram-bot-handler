<?php

declare(strict_types=1);

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\ClosureMessageCommand;
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};
use Mateodioev\TgHandler\{Bot, Context};

require __DIR__ . '/../vendor/autoload.php';

$bot = new Bot($_ENV['BOT_TOKEN'], new Logger(new TerminalStream())); // put your bot token here

$bot->setLogger(new Logger(new TerminalStream()));

// New closure must receive Api and Context params
$startCommand = function (Api $bot, Context $ctx, array $args = []) {
    $bot->replyTo($ctx->getChatId(), 'Hello world!', $ctx->getMessageId());
};

// create MessageCommand from closure

$bot->registerCommand(
    ClosureMessageCommand::fromClosure(name: 'start', fn: $startCommand)
        ->setPrefixes(['/', '.', '!'])
);

$bot->registerCommand(
    ClosureMessageCommand::fromClosure(name: 'notes', fn: function (Api $bot, Context $ctx, array $args = []) {
        $bot->replyTo($ctx->getChatId(), 'Find your notes here', $ctx->getMessageId());
    })->setPrefixes(['#'])
);

// set timeout to 20s and run in async mode
$bot->longPolling(20, false, true);
