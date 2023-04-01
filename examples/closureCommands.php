<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};
use Mateodioev\TgHandler\{Bot, Context};

require __DIR__ . '/../vendor/autoload.php';

$bot = new Bot($_ENV['BOT_TOKEN'] ?? '2072676984:AAH8GvcBv-BiJAVdBX82NFf-ASLMI8er_Ig'); // put your bot token here

$bot->setLogger(new Logger(new TerminalStream));

// New closure must receive Api and Context params
$startCommand = function (Api $bot, Context $ctx) {
    $bot->replyTo($ctx->getChatId(), 'Hello world!', $ctx->getMessageId(), 'HTML');
};

// create MessageCommand from closure
$bot->onCommand('start', $startCommand)->setPrefixes(['/', '.', '!']);

$bot->onCommand('notes', function (Api $bot, Context $ctx) {
    $bot->replyTo($ctx->getChatId(), 'Find your notes heres', $ctx->getMessageId());
    # ... implementes your logic
})->setPrefixes(['#']);

// set timeout to 20s and run in async mode
$bot->longPolling(20, false, true);
