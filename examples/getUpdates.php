<?php

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/functions.php';

// Support for /start, !start, .start
$cmd = new Commands(cmd_prefix: ['/', '!', '.']);
$cmd->setBotUsername('your bot username');

// https://telegram.me/botfather
$bot = new Methods('your telegram bot token');

$cmd->on('message', 'onText');
$cmd->CmdMessage('start', 'sendHelloWithButton', [$bot]);

noWebhook($bot, $cmd);
