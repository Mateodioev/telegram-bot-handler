<?php

use Mateodioev\Bots\Telegram\{Methods, Buttons};
use Mateodioev\TgHandler\Commands;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/functions.php';

// Support for /start, !start, .start
$cmd = new Commands(cmd_prefix: ['/', '!', '.']);
$cmd->setBotUsername('your bot username');

// https://telegram.me/botfather
$bot = new Methods('your telegram bot token');

// Middleware on message
$cmd->on('message', 'sendHelloWithButton', [$bot]);

$update = json_decode(file_get_contents('php://input')) or die('No update');
$cmd->setUpdate($update);

runBot($cmd);