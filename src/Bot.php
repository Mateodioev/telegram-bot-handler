<?php

namespace Mateodioev\TgHandler;

use Closure;
use Exception;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Types\Update;
use Mateodioev\TgHandler\Commands\CommandInterface;
use Mateodioev\TgHandler\Log\{BotApiStream, Logger};
use Mateodioev\TgHandler\Commands\StopCommand;
use Psr\Log\LoggerInterface;

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
     * Set default logger class
     * @param string $chatId Chat to send logs
     */
    public function setDefaultLogger(string $chatId): Bot
    {
        $apiStream = new BotApiStream($this->getApi(), $chatId);
        return $this->setLogger(new Logger($apiStream));
    }

    /**
     * @throws Exception
     */
    public function getLogger(): LoggerInterface
    {
        try {
            return $this->logger;
        } catch (\Throwable $e) {
            throw new Exception('Logger not set');
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

    public function run(Update $update): void
    {
        $ctx = Context::fromUpdate($update);
        // Get context properties as array
        $ctxProperties = $ctx->get();

        foreach ($ctxProperties as $type => $value) {
            if (!is_array($value))
                continue;

            $commands = $this->commands[$type] ?? [];
            foreach ($commands as $command) {
                try {
                    $params = $this->handleMiddlewares($command, $ctx);
                    $command->setLogger($this->getLogger())
                        ->execute($this->api, $ctx, $params);
                } catch (\Throwable $e) {
                    if ($this->handleException($e, $this, $ctx)) continue;

                    $this->logger->error('Fail to run command {name}, reason: {reason}', [
                        'name' => $command->getName(),
                        'reason' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    public function byWebhook(): void
    {
        $update = json_decode(
            file_get_contents('php://input')
        );
        $update = new Update($update);

        $this->run($update);
    }

    public function longPolling(int $timeout): never
    {
        $offset = 0;

        // Get updates only for registered commands
        $allowedUpdates = \array_keys($this->commands);
        while (true) {

            try {
                $updates = $this->api->getUpdates($offset, 100, $timeout, $allowedUpdates);
            } catch (\Throwable $e) {
                $this->logger->warning('Fail to get updates: {reason}', ['reason' => $e->getMessage()]);
                continue;
            }

            foreach ($updates as $update) {
                $offset = $update->update_id() + 1;
                $this->run($update);
            }
        }
    }
}
