<?php

declare(strict_types=1);

use Amp\ByteStream;
use Amp\Http\HttpStatus;
use Amp\Http\Server\{DefaultErrorHandler, Request, RequestHandler, Response, SocketHttpServer};
use Amp\Log\{ConsoleFormatter, StreamHandler};
use Mateodioev\TgHandler\{Bot, Context};
use Mateodioev\Utils\Exceptions\RequestException;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

require __DIR__ . '/bootstrap.php';

checkDependency(ConsoleFormatter::class, 'amphp/log');
checkDependency(Request::class, 'amphp/http-server');
checkDependency(Logger::class, 'monolog/monolog');

//
// LOGGER
//

$logHandler = new StreamHandler(ByteStream\getStdout());
$logHandler->pushProcessor(new PsrLogMessageProcessor());
$logHandler->setFormatter(new ConsoleFormatter());

$botLogHandler = clone $logHandler;
$botLogHandler->setFormatter(new ConsoleFormatter(format: "[%datetime%] %channel%.%level_name%: %message%\r\n", ignoreEmptyContextAndExtra: true, allowInlineLineBreaks: true));

$logger = new Logger('bot');
$logger->pushHandler($botLogHandler);


//
// BOT
//

$bot = Bot::fromConfig($config);
$bot->setLogger($logger);

// Exception handler for RequestException
$bot->setExceptionHandler(RequestException::class, function (RequestException $e, Bot $bot, Context $ctx) {
    $bot->getLogger()->error($e::class . ': ' . $e->getMessage());
});

$bot->onEvent(new Message())
    ->onEvent(new TestFilters())
    ->onEvent(new All())
    ->onEvent(new StickerListener())
    ->onEvent(Start::get())
    ->onEvent(ButtonCallback::get())
    ->onEvent(Params::get())
    ->onEvent(Name::get());

//
// SERVER
//

$requestHandler =  new class ($bot, $logger) implements RequestHandler {
    public function __construct(private Bot $bot, private Logger $logger)
    {
    }

    public function handleRequest(Request $request): Response
    {
        $a = $request->getBody()->read();

        if (empty($a)) {
            return new Response(
                status: HttpStatus::OK,
                headers: ['content-type' => 'text/plain; charset=utf-8'],
                body: 'Ok'
            );
        }

        $this->logger->debug('Webhook received: {payload}', ['payload' => $a]);

        try {
            $this->bot->byWebhook(json_decode($a, true), async: true, disableStateCheck: true);
        } catch (\Throwable $e) {
            $this->logger->error($e::class . ': ' . $e->getMessage());
        }

        return new Response(
            status: HttpStatus::OK,
            headers: ['content-type' => 'text/plain; charset=utf-8'],
            body: 'Ok'
        );
    }
};

$logger = new Logger('server');
$logger->pushHandler($logHandler);

$errorHandler = new DefaultErrorHandler();

$server = SocketHttpServer::createForDirectAccess($logger);
$server->expose('127.0.0.1:1337');
$server->start($requestHandler, $errorHandler);

// Serve requests until SIGINT or SIGTERM is received by the process.
Amp\trapSignal([SIGINT, SIGTERM]);

$server->stop();


function checkDependency(string $classTest, string $pkg, int $exitCode = 1): void
{
    if (\class_exists($classTest) === false) {
        echo 'Please install ' . $pkg . ' package' . PHP_EOL;
        exit($exitCode);
    }
}
