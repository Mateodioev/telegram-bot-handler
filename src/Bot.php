<?php

namespace Mateodioev\TgHandler;

use Closure, Exception, Throwable;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Types\{Update, Error};
use Mateodioev\TgHandler\Conversations\Conversation;
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};
use Mateodioev\TgHandler\Events\{EventInterface, EventType, TemporaryEvent};
use Mateodioev\TgHandler\Commands\{StopCommand, ClosureMessageCommand};
use Mateodioev\TgHandler\Db\{DbInterface, Memory};
use Mateodioev\Bots\Telegram\Exception\TelegramApiException;
use Psr\Log\LoggerInterface;

use function Amp\async;
use function Amp\Future\awaitAll;
use function array_merge, call_user_func;

class Bot
{
    use middlewares;

    private const EVENTS_CACHE = 'events.cache.json';

    private Api $api;
    private LoggerInterface $logger;
    private ?DbInterface $db = null;

    private EventStorage $eventStorage;

    /** @var array<string,Closure> */
    private array $exceptionHandlers = [];

    /** @var RunState Bot run mode */
    public static RunState $state = RunState::none;

    public function __construct(string $token)
    {
        $this->api = new Api($token);
        $this->eventStorage = new EventStorage;

        $this->setExceptionHandler(StopCommand::class, StopCommand::handler(...));
    }

    /**
     * Create new Bot instance from config class
     */
    public static function fromConfig(BotConfig $config): Bot
    {
        $bot = (new static($config->token()))
            ->setDb($config->db())
            ->setLogger($config->logger());

        $bot->getApi()->setAsync($config->async());
        return $bot;
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
        // $stream = new PhpNativeStream;
        return $this->setLogger(new Logger(new TerminalStream));
    }

    /**
     * If logger is not set, create new PhpNativeStream
     */
    public function getLogger(): LoggerInterface
    {
        try {
            return $this->logger;
        } catch (Throwable) {
            return $this->setDefaultLogger()->logger;
        }
    }

    public function setDb(DbInterface $db): Bot
    {
        $this->db = $db;
        return $this;
    }

    protected function getDb(): DbInterface
    {
        if ($this->db instanceof DbInterface)
            return $this->db;

        $this->db = new Memory; // Default database
        return $this->db;
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

    private function findExceptionHandler(Throwable $exception): ?Closure
    {
        $exceptionName = $exception::class;
        $handler = $this->exceptionHandlers[$exceptionName] ?? null;

        if ($handler !== null)
            return $handler;

        foreach ($this->exceptionHandlers as $name => $exceptionHandler) {
            if (\is_a($exception, $name)) // Check is same class or subclass
                return $exceptionHandler;
        }

        return null;
    }

    /**
     * @return bool Return true if exception handled
     */
    protected function handleException(Throwable $e, Bot $api, Context $ctx): bool
    {
        $handler = $this->findExceptionHandler($e);

        if ($handler === null)
            return false;

        call_user_func($handler, $e, $api, $ctx);
        return true;
    }

    /**
     * Register new event
     */
    public function onEvent(EventInterface $event): Bot
    {
        $this->eventStorage->add($event);
        return $this;
    }

    /**
     * @deprecated use onEvent
     * @throws Exception is `$type` is invalid Event type
     */
    public function on(string $type, EventInterface $command): Bot
    {
        return $this->onEvent($command);
    }

    public function onCommand(string $name, Closure $fn): ClosureMessageCommand
    {
        $command = ClosureMessageCommand::fromClosure(name: $name, fn: $fn);
        $this->onEvent($command);
        return $command;
    }

    /**
     * Get commands
     * @return EventInterface[]
     */
    protected function resolveEvents(Context $ctx): array
    {
        return array_merge(
            $this->eventStorage->resolve($ctx->eventType()),
            $this->eventStorage->resolve(EventType::all) // tg not send this event
        );
    }

    protected function deleteEvent(EventInterface $event): void
    {
        $this->eventStorage->delete($event);
    }

    /**
     * Execute middlewares and command
     */
    public function executeCommand(EventInterface $event, Context $ctx): void
    {
        $api = $this->getApi();
        try {
            $event->setVars($api, $ctx);

            if ($event->isValid() === false || $event->validateFilters() === false) {
                // Invalid event
                $this->getLogger()->debug(
                    'It\'s not possible to validate the event {name} ({type})',
                    [
                        'type' => $event->type()->prettyName(),
                        'name' => $event::class
                    ]
                );
                return;
            }

            $return = $event->setLogger($this->getLogger())->execute(
                $this->handleMiddlewares($event, $ctx)
            );

            // Delete temporary event
            if ($event instanceof TemporaryEvent) {
                $this->deleteEvent($event);
            }
            // Register next conversation
            if ($return instanceof Conversation) {
                $this->onEvent($return);
            }
        } catch (Throwable $e) {
            if ($this->handleException($e, $this, $ctx))
                return;

            $this->getLogger()->error('Fail to run {name} ({eventType}), reason: {reason} on {file}:{line}', [
                'name'      => $event::class,
                'eventType' => $event->type()->prettyName(),
                'reason'    => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine()
            ]);
        }
    }

    /**
     * Run commands
     */
    public function run(Update $update): void
    {
        $ctx = Context::fromUpdate($update);

        array_map(function (EventInterface $event) use ($ctx) {
            $this->executeCommand($event, $ctx);
        }, $this->resolveEvents($ctx));
    }

    /**
     * Run commands in async mode
     */
    public function runAsync(Update $update): void
    {
        $ctx = Context::fromUpdate($update);

        awaitAll(
            array_map(function (EventInterface $event) use ($ctx) {
                // create async function for each command
                return async(function (EventInterface $event, Context $ctx) {
                    $this->executeCommand($event, $ctx);
                }, $event, $ctx);
            }, $this->resolveEvents($ctx))
        );
    }

    /**
     * Run bot in webhook mode
     *
     * @param array $up Update array
     * @param bool $async Run in async mode using AMPHP
     */
    public function byWebhook(array $up, bool $async = false): void
    {
        self::$state = RunState::webhook;

        $update = new Update($up);

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
        self::$state = RunState::longpolling;

        $offset = ($ignoreOldUpdates) ? -1 : 0;

        // Get updates only for registered commands
        $allowedUpdates = $this->getAllowedUpdates();

        $this->getApi()->setAsync($async);

        // enable garbage collector
        gc_enable();
        while (true) {

            try {
                /** @var Update[]|Error $updates */
                $updates = $this->getApi()->getUpdates($offset, 100, $timeout, $allowedUpdates);
                if ($updates instanceof Error)
                    throw new TelegramApiException('(' . ($updates->error_code ?? 0) . ') ' . ($updates->description ?? ''));
            } catch (TelegramApiException $e) {
                if ($e->getCode() === 404 || $e->getCode() === 401) { // 401 unauthorized or 404 not found
                    $this->getLogger()->critical('Invalid bot token');
                    exit(1);
                }
                $this->getLogger()->warning('Fail to get updates: {reason}', ['reason' => $e->getMessage()]);
                sleep(1);
                continue;
            }

            if ($async) {
                array_map(function (Update $update) use (&$offset) {
                    $offset = $update->updateId() + 1;
                    async(fn (Update $up) => $this->runAsync($up), $update);
                }, $updates);

                \Amp\delay(1);
            } else {
                array_map(function (Update $update) use (&$offset) {
                    $offset = $update->updateId() + 1;
                    $this->run($update);
                }, $updates);
            }

            unset($updates);
            gc_collect_cycles();
        }
    }

    private function getAllowedUpdates(): array
    {
        return $this->eventStorage->types();
    }
}
