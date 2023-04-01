<?php

namespace Mateodioev\TgHandler;

use Closure, Exception;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Exception\TelegramApiException;
use Mateodioev\Bots\Telegram\Types\Update;
use Mateodioev\TgHandler\Commands\CommandInterface;
use Mateodioev\TgHandler\Log\{Logger};
use Mateodioev\TgHandler\Commands\StopCommand;
use Psr\Log\LoggerInterface;
use function Amp\async;
use function Amp\Future\awaitAll;
use Mateodioev\TgHandler\Log\PhpNativeStream;
use Mateodioev\TgHandler\Commands\ClosureMessageCommand;

class Bot
{
    use middlewares;

    protected Api $api;
    protected LoggerInterface $logger;

    /**
     * @var array<string|CommandInterface[]>
     */
    protected array $commands = [];
    /**
     * @var array<string,Closure>
     */
    protected array $exceptionHandlers = [];

    public function __construct(string $token)
    {
        $this->api = new Api($token);

        $this->setExceptionHandler(StopCommand::class, StopCommand::handler(...));
    }

    public function getApi(): Api
    {
        return $this->api;
    }

    public function setLogger(LoggerInterface $logger): Bot
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set default logger class to PhpNativeStream
     */
    public function setDefaultLogger(): Bot
    {
        $stream = new PhpNativeStream;
        return $this->setLogger(new Logger($stream->activate(__DIR__)));
    }

    /**
     * If logger is not set, create new PhpNativeSream 
     */
    public function getLogger(): LoggerInterface
    {
        try {
            return $this->logger;
        } catch (\Throwable $e) {
            return $this->setDefaultLogger()->logger;
        }
    }

    /**
     * @param string $exceptionName Exception class name
     * @param Closure $handler Handler function, must accept 3 arguments: \Throwable $e, Bot $api, Context $ctx
     * @return Bot
     */
    public function setExceptionHandler(string $exceptionName, Closure $handler): Bot
    {
        $this->exceptionHandlers[$exceptionName] = $handler;
        return $this;
    }

    /**
     * @return bool Return true if exception handled
     */
    protected function handleException(\Throwable $e, Bot $api, Context $ctx): bool
    {
        $exceptionName = $e::class;
        if (isset($this->exceptionHandlers[$exceptionName])) {
            $handler = $this->exceptionHandlers[$exceptionName];
            call_user_func($handler, $e, $api, $ctx);
            return true;
        }

        return false;
    }

    public function on(string $type, CommandInterface $command): Bot
    {
        $this->commands[$type][] = $command;
        return $this;
    }

    public function onCommand(string $name, Closure $fn): ClosureMessageCommand
    {
        $command = ClosureMessageCommand::fromClosure($fn, $name);
        $this->on('message', $command);
        return $command;
    }

    /**
     * Get commands
     * @return CommandInterface[]
     */
    protected function resolveCommands(Context $ctx): array
    {
        // get context properties as array
        $ctxProperties = $ctx->get();
        $commands = [];

        foreach ($ctxProperties as $type => $value) {
            if (!is_array($value)) continue;

            // add commands to return
            foreach (($this->commands[$type] ?? []) as $command) {
                $commands[] = $command;
            }
        }
        return $commands;
    }

    /**
     * Execute middlewares and command
     */
    public function executeCommand(CommandInterface $command, Context $ctx): void
    {
        try {
            if (!$command->isValid($this->getApi(), $ctx)) return;

            $params = $this->handleMiddlewares($command, $ctx);
            $command->setLogger($this->getLogger())
                ->execute($this->getApi(), $ctx, $params);
        } catch (\Throwable $e) {
            if ($this->handleException($e, $this, $ctx)) return;

            $this->getLogger()->error('Fail to run command {name}, reason: {reason}', [
                'name' => $command->getName(),
                'reason' => $e->getMessage()
            ]);
        }
    }

    /**
     * Run commands
     */
    public function run(Update $update): void
    {
        $ctx = Context::fromUpdate($update);

        array_map(function (CommandInterface $command) use ($ctx) {
            $this->executeCommand($command, $ctx);
        }, $this->resolveCommands($ctx));
    }

    /**
     * Run commands in async mode
     */
    public function runAsync(Update $update): void
    {
        $ctx = Context::fromUpdate($update);

        awaitAll(
            array_map(function (CommandInterface $command) use ($ctx) {
                // create async function for each command
                return async(function (CommandInterface $command, Context $ctx) {
                    $this->executeCommand($command, $ctx);
                }, $command, $ctx);
            }, $this->resolveCommands($ctx))
        );
    }

    public function byWebhook(bool $async = false): void
    {
        $update = json_decode(
            file_get_contents('php://input')
        );
        $update = new Update($update);

        $this->getApi()->setAsync($async);
        $async ? $this->runAsync($update) : $this->run($update);
    }

    /**
     * Run bot in long polling mode
     *
     * @param integer $timeout Timeout in seconds
     * @param boolean $ignoreOldUpdates Ignore old updates
     * @param boolean $async Run in async mode using AMPHP
     */
    public function longPolling(int $timeout, bool $ignoreOldUpdates = false, bool $async = false): never
    {
        $offset = ($ignoreOldUpdates) ? -1 : 0;

        // Get updates only for registered commands
        $allowedUpdates = \array_keys($this->commands);

        $this->getApi()->setAsync($async);

        while (true) {

            try {
                /** @var Update[] */
                $updates = $this->getApi()->getUpdates($offset, 100, $timeout, $allowedUpdates);
            } catch (TelegramApiException $e) {
                if ($e->getCode() == 404) {
                    $this->getLogger()->critical('Invalid bot token');
                    exit(1);
                }
                $this->getLogger()->warning('Fail to get updates: {reason}', ['reason' => $e->getMessage()]);
                continue;
            }

            if ($async) {
                awaitAll(
                    array_map(function (Update $update) use (&$offset) {
                        $offset = $update->updateId() + 1;
                        return async(getAsyncFn(), $update, $this);
                    }, $updates)
                );
            } else {
                array_map(function (Update $update) use (&$offset) {
                    $offset = $update->updateId() + 1;
                    $this->run($update);
                }, $updates);
            }
        }
    }
}

function getAsyncFn(): Closure
{
    return static function (Update $up, Bot &$instance) {
        $instance->runAsync($up);
    };
}