<?php

declare (strict_types=1);

namespace Mateodioev\TgHandler;

use Closure;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Exception\TelegramApiException;
use Mateodioev\Bots\Telegram\Types\{Error, Update};
use Mateodioev\TgHandler\Commands\Generics\{GenericCallbackCommand, GenericCommand, GenericMessageCommand};
use Mateodioev\TgHandler\Commands\{Command, StopCommand};
use Mateodioev\TgHandler\Conversations\Conversation;
use Mateodioev\TgHandler\Db\{DbInterface, Memory};
use Mateodioev\TgHandler\Events\{EventInterface, EventType, TemporaryEvent};
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;
use Throwable;

use function Amp\async;
use function Amp\Future\awaitAll;
use function array_map;

class Bot
{
    use middlewares;

    private const EVENTS_CACHE = 'events.cache.json';

    /** @var RunState Bot run mode */
    public static RunState $state = RunState::none;

    private Api $api;
    private LoggerInterface $logger;
    private ?DbInterface $db = null;

    private EventStorage $eventStorage;

    /** @var array<string,Closure> */
    private array $exceptionHandlers = [];

    /** @var array<string, GenericCommand> */
    private array $genericCommands = [];

    public function __construct(string $token, LoggerInterface $logger)
    {
        $this->setLogger($logger);
        $this->setExceptionHandler(StopCommand::class, StopCommand::handler(...));

        $this->api = new Api($token);
        $this->eventStorage = new EventStorage();

        $this->genericCommands = [
            EventType::message->value() => new GenericMessageCommand($this),
            EventType::callback_query->value() => new GenericCallbackCommand($this),
        ];
        foreach ($this->genericCommands as $generic) {
            $this->eventStorage->add($generic);
        }
    }

    /**
     * Create new Bot instance from config class
     */
    public static function fromConfig(BotConfig $config): Bot
    {
        $bot = (new static($config->token(), $config->logger()))
            ->setDb($config->db());

        $bot->getApi()->setAsync($config->async());

        $bot->getLogger()->debug('Bot created from config {config}', ['config' => $config::class]);
        return $bot;
    }

    public function getApi(): Api
    {
        return $this->api;
    }

    public function setLogger(LoggerInterface $logger): Bot
    {
        $logger->debug('Set logger {name}', ['name' => $logger::class]);
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set default logger class to PhpNativeStream
     */
    public function setDefaultLogger(): Bot
    {
        // $stream = new PhpNativeStream;
        return $this->setLogger(new Logger(new TerminalStream()));
    }

    /**
     * If logger is not set, create new PhpNativeStream
     */
    public function getLogger(): LoggerInterface
    {
        try {
            return $this->logger;
        } catch (Throwable) {
            $logger = $this->setDefaultLogger()->logger;
            $logger->debug('Set default logger {logger} with {stream}', ['logger' => $logger::class, 'stream' => TerminalStream::class]);
            return $logger;
        }
    }

    public function setDb(DbInterface $db): Bot
    {
        $this->getLogger()->debug('Set db {name}', ['name' => $db::class]);
        $this->db = $db;
        return $this;
    }

    protected function getDb(): DbInterface
    {
        if ($this->db instanceof DbInterface) {
            return $this->db;
        }

        // Default db
        return $this->setDb(new Memory())->db;
    }

    /**
     * @param string $exceptionName Exception class name
     * @param Closure $handler Handler function, must accept 3 arguments: \Throwable $e, Bot $api, Context $ctx
     * @return Bot
     */
    public function setExceptionHandler(string $exceptionName, Closure $handler): Bot
    {
        $this->getLogger()->info('Register exception handler for {exceptionName}', ['exceptionName' => $exceptionName]);
        $this->exceptionHandlers[$exceptionName] = $handler;
        return $this;
    }

    private function findExceptionHandler(Throwable $exception): ?Closure
    {
        $exceptionName = $exception::class;
        $handler = $this->exceptionHandlers[$exceptionName] ?? null;

        if ($handler !== null) {
            return $handler;
        }

        foreach ($this->exceptionHandlers as $name => $exceptionHandler) {
            if (is_a($exception, $name)) { // Check is same class or subclass
                return $exceptionHandler;
            }
        }

        return null;
    }

    /**
     * @return bool Return true if exception handled
     */
    public function handleException(Throwable $e, Bot $api, Context $ctx): bool
    {
        $handler = $this->findExceptionHandler($e);

        if ($handler === null) {
            return false;
        }

        call_user_func($handler, $e, $api, $ctx);
        $this->getLogger()->info('Exception "{e}" handled', ['e' => $e::class]);
        return true;
    }

    /**
     * Register new event
     */
    public function onEvent(EventInterface $event): Bot
    {
        $this->registerEvent($event);
        return $this;
    }

    /**
     * Register new event.
     * @return int Event id, return -1 if event is a command
     */
    private function registerEvent(EventInterface $eventInterface): int
    {
        if ($eventInterface instanceof Command) {
            $this->registerCommand($eventInterface);
            return -1;
        }

        $this->getLogger()->debug('Register event {name} ({type})', [
            'type' => $eventInterface->type()->prettyName(),
            'name' => $eventInterface::class,
        ]);

        return $this->eventStorage->add($eventInterface);
    }

    /**
     * Register a conversation with ttl. If ttl is Conversation::UNDEFINED_TTL, the conversation will not be registered.
     * @internal
     */
    public function registerConversation(Conversation $conversation): void
    {
        $ttl = $conversation->ttl();
        $conversationId = $this->registerEvent($conversation);

        if ($ttl === Conversation::UNDEFINED_TTL) {
            return;
        }

        $this->getLogger()->info('Conversation {name} with id {id} will be removed after {ttl} seconds', [
            'name' => $conversation::class,
            'id' => $conversationId,
            'ttl' => $ttl,
        ]);

        $id = EventLoop::delay($ttl, function () use ($conversationId, $conversation) {
            $deleted = $this->eventStorage->deleteById($conversationId);

            if ($deleted) {
                $this->getLogger()->debug(
                    message: 'Conversation with id {id} removed because exceded the ttl ({ttl})',
                    context: ['id' => $conversationId, 'ttl' => $conversation->ttl()]
                );
                EventLoop::queue($conversation->onExpired(...));
            }
        });
    }

    /**
     * @throws BotException If command type is invalid
     */
    public function registerCommand(Command $command): GenericCommand
    {
        $type = $command->type();
        $generic = $this->genericCommands[$type->value()] ?? throw new BotException('Invalid command type: ' . $type->prettyName());

        $generic->add($command);

        return $generic;
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

    public function deleteEvent(EventInterface $event): void
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
            $event->setVars($api, $ctx)
                ->setDb($this->getDb());

            if ($event->isValid() === false || $event->validateFilters() === false) {
                // Invalid event
                $this->getLogger()->debug(
                    'It\'s not possible to validate the event {name} ({type})',
                    [
                        'type' => $event->type()->prettyName(),
                        'name' => $event::class,
                    ]
                );
                return;
            }

            $nextEvent = $event->setLogger($this->getLogger())->execute(
                $this->handleMiddlewares($event, $ctx)
            );

            // Delete temporary event
            if ($event instanceof TemporaryEvent) {
                $this->deleteEvent($event);
            }
            // Register next conversation
            if ($nextEvent instanceof Conversation) {
                $this->getLogger()->info('Register next conversation {name}', ['name' => $nextEvent::class]);
                $this->registerConversation(
                    $nextEvent->setVars($api, $ctx)
                        ->setDb($this->getDb())
                );
            }
        } catch (Throwable $e) {
            if ($this->handleException($e, $this, $ctx)) {
                return;
            }

            $this->getLogger()->error('Fail to run {name} ({eventType}), reason: {reason} on {file}:{line}', [
                'name' => $event::class,
                'eventType' => $event->type()->prettyName(),
                'reason' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
            ]);
        }
    }

    /**
     * Run commands
     */
    public function run(Update $update): void
    {
        $ctx = Context::fromUpdate($update)->withLogger($this->getLogger());

        array_map(function (EventInterface $event) use ($ctx) {
            $this->executeCommand($event, $ctx);
        }, $this->resolveEvents($ctx));
    }

    /**
     * Run commands in async mode
     */
    public function runAsync(Update $update): void
    {
        $ctx = Context::fromUpdate($update)->withLogger($this->getLogger());

        awaitAll(
            // Create Futures of all commands
            array_map(function (EventInterface $event) use ($ctx) {
                // create async function for each command
                return async(function (EventInterface $event, Context $ctx) {
                    $this->executeCommand($event, $ctx);
                }, $event, $ctx);
            }, $this->resolveEvents($ctx))
        );
        // Wait all futures
    }

    /**
     * Run bot in webhook mode
     *
     * @param array $up Update array
     * @param bool $async Run in async mode using AMPHP
     * @param bool $disableStateCheck Ignore status setting to webhook mode (Useful for conversations or use of Db\Memory)
     */
    public function byWebhook(array $up, bool $async = false, bool $disableStateCheck = false): void
    {
        if ($disableStateCheck) {
            $this->getLogger()->notice('Disable state check');
            $this->getLogger()->debug('Bot current state: {state}', ['state' => self::$state]);
        } else {
            self::$state = RunState::webhook;
        }

        $update = new Update($up);

        $this->handleUpdate($update, $async);
    }

    /**
     * Handle the given update
     */
    public function handleUpdate(Update $update, bool $async = false): void
    {
        $this->getApi()->setAsync($async);

        $async
        ? $this->runAsync($update)
        : $this->run($update);
    }

    /**
     * Run bot in long polling mode
     *
     * @param integer $timeout Timeout in seconds
     * @param boolean $ignoreOldUpdates Ignore old updates
     * @param boolean $async Run in async mode using AMPHP
     */
    public function longPolling(int $timeout, bool $ignoreOldUpdates = false, bool $async = false): void
    {
        self::$state = RunState::longpolling;

        $offset = ($ignoreOldUpdates) ? -1 : 0;

        // Get updates only for registered commands
        $allowedUpdates = $this->eventStorage->types();
        $this->getLogger()->info('Allowed updates: {updates}', ['updates' => json_encode($allowedUpdates)]);

        $this->getApi()->setAsync($async);

        // enable garbage collector
        gc_enable();
        while (true) {
            if (RunState::stop === self::$state) {
                $this->getLogger()->notice('Bot stop');
                break;
            }

            try {
                /** @var Update[]|Error $updates */
                $updates = $this->getApi()->getUpdates($offset, 100, $timeout, $allowedUpdates);
                if ($updates instanceof Error) {
                    $this->getLogger()->emergency('Fail to get updates: {error}', ['error' => $updates->description]);
                    throw new TelegramApiException('(' . ($updates->error_code ?? 0) . ') ' . ($updates->description ?? ''));
                }
            } catch (TelegramApiException $e) {
                if ($e->getCode() === 404 || $e->getCode() === 401) { // 401 unauthorized or 404 not found
                    $this->getLogger()->critical('Invalid bot token');
                    self::terminate();
                    continue;
                }

                $this->getLogger()->warning('Fail to get updates: {reason}', ['reason' => $e->getMessage()]);
                sleep(1);
                continue;
            }

            $offset = $this->queueUpdates($updates, $offset, $async);
            unset($updates);

            gc_collect_cycles();
        }
        if ($async === false) {
            return;
        }

        \Amp\delay(1);
        EventLoop::getDriver()->stop();
    }

    private function queueUpdates(array $updates, int $offset, bool $async): int
    {
        if ($async) {
            // queue to the event loop
            async(function () use ($updates, &$offset) {
                // Add all the updates to the event loop and run in the next tick
                array_map(function (Update $update) use (&$offset): void {
                    $offset = $update->updateId() + 1;
                    EventLoop::defer(function ($callbackId) use ($update) {
                        $this->runAsync($update);
                    });
                }, $updates);
                \Amp\delay(1);
            })->await();
        } else {
            // run in the same thread
            array_map(function (Update $update) use (&$offset): void {
                $offset = $update->updateId() + 1;
                $this->run($update);
            }, $updates);
        }

        return $offset;
    }

    /**
     * Stop the bot in the next iteration.
     * Only works in long polling mode
     * @protected
     */
    public static function terminate(): void
    {
        self::$state = RunState::stop;
    }
}
