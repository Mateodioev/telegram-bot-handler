<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Commands\Generics\FallbackCallbackCommand;
use Mateodioev\TgHandler\Db\Memory;
use Mateodioev\TgHandler\Log\{BotApiStream, Logger};
use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\TgHandler\Log\BotApiStreamConfig;
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
    ->onEvent(new StickerListener());

// Register text command
$bot->registerCommand(new Start())
    ->add(new DynamicStart())
    ->add(new Params())
    ->add(new Name())
    ->add(new TriggerConversationCommand())
    ->add(new Me())
    ->add(new GetUsage())
    ->withDefaultFallbackCommand(); // use this to register the fallback command

// Register callback command
$bot->registerCommand(new ButtonCallback())
    ->setFallbackCommand(new FallbackCallbackCommand());

$streamCollection->add(new BotApiStream(
    BotApiStreamConfig::default($config->token(), '996202950')
));
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
