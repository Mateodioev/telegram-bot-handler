<?php

use Mateodioev\Bots\Telegram\{Methods, Buttons};
use Mateodioev\TgHandler\Commands;

function sendHello(Methods $bot, Commands $cmd): stdClass {
  return $bot->sendMessage($cmd->getChatId(), 'Hello World!');
}

function sendHelloWithButton(Methods $bot, Commands $cmd): stdClass {
  $bot->AddOpt([
    'reply_markup' => (string) Buttons::create()
      ->addCeil(['text' => 'Open this link', 'url' => 'https://google.com'])
  ]);

  return sendHello($bot, $cmd);
}

function onText(Commands $cmd) {
  echo 'Function executed on text messages.' . PHP_EOL;
  echo 'User id: ' . $cmd->getUserId() . ' | Chat id: ' . $cmd->getChatId() . PHP_EOL;
  echo 'Text: ' . $cmd->getText() . PHP_EOL . PHP_EOL;
}

function runBot(Commands $cmd) {
  try {
    $cmd->Run(function (Commands $cmd) {
      echo 'Command result: ' . var_export($cmd->getFnResult(), true) . PHP_EOL;
    });
  } catch (\Throwable $th) {
    echo 'Error: ' . $th->getMessage() . PHP_EOL;
  }
}

function noWebhook(Methods $bot, Commands $cmd) {
  $payload = ['offset' => 0];
  // Infinite loop
  while (true) {
    $updates = $bot->getUpdates($payload);

    if (!$updates->ok) {
      echo 'Error: ' . $updates->description . PHP_EOL;
      break;
    }

    foreach ($updates->result as $up) {
      $payload['offset'] = $up->update_id + 1;
      $cmd->setUpdate($up);
      runBot($cmd);
    }
  }

}