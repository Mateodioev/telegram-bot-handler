<?php

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\TgHandler\Commands;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/functions.php';

$cmd = new Commands(cmd_prefix: ['/', '!', '.']); // Support for /start, !start, .start
$cmd->setBotUsername('your bot username');

$bot = new Methods('your telegram bot token'); // https://telegram.me/botfather

// Register new message command
$cmd->CmdMessage('start', 'sendHello', [$bot]);

$update = json_decode(file_get_contents('php://input')) or die('No update');
$cmd->setUpdate($update);

runBot($cmd);
