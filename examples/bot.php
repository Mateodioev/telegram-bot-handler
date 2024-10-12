<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Commands\Generics\FallbackCallbackCommand;
use Mateodioev\TgHandler\Db\Memory;
use Mateodioev\TgHandler\Log\{BotApiStream, Logger};
use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\Utils\Exceptions\RequestException;

require __DIR__ . '/bootstrap.php';

$bot = Bot::fromConfig($config);
$bot->setDb($db = new Memory());
$db->save('memory_usage', memory_get_usage());

// Exception handler for RequestException
$bot->setExceptionHandler(RequestException::class, function (RequestException $e, Bot $bot, Context $ctx) {
    $bot->getLogger()->error('New Request exception', ['exception' => $e]);
});

$bot->onEvent(new Message())
    ->onEvent(new TestFilters())
    ->onEvent(new All())
    ->onEvent(new StickerListener())
    ->onEvent(ButtonCallback::get());

// Register text command
$bot->registerCommand(Start::get())
    ->add(DynamicStart::get())
    ->add(Params::get())
    ->add(Name::get())
    ->add(TriggerConversationCommand::get())
    ->add(Me::get())
    ->add(GetUsage::get())
    ->withDefaultFallbackCommand(); // use this to register the fallback command

// Register callback command
$bot->registerCommand(ButtonCallback::get())
    ->setFallbackCommand(new FallbackCallbackCommand());

$streamCollection->add(new BotApiStream($bot->getApi(), '996202950'));
$bot->setLogger(new Logger($streamCollection));

try {
    $bot->longPolling(
        timeout: 60,
        ignoreOldUpdates: true,
        async: $config->async()
    );
} catch (Exception $e) {
    echo $e;
}
