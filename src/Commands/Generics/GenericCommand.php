<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands\Generics;

use Amp\DeferredCancellation;
use Closure;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\Command;
use Mateodioev\TgHandler\Conversations\Conversation;
use Mateodioev\TgHandler\Events\{TemporaryEvent, abstractEvent};
use Mateodioev\TgHandler\{Bot, Context};
use Revolt\EventLoop;
use Throwable;

use function Amp\async;
use function Amp\Future\await;

abstract class GenericCommand extends abstractEvent
{
    /**
     * @var Command[]
     */
    public array $commands = [];
    public ?FallbackCommand $fallbackCommand = null;

    public function __construct(protected Bot $bot)
    {
        $this->setLogger($bot->getLogger());
    }

    public function execute($args = []): void
    {
        $handled = false;                     // If any command is handled set to true
        $cancellation = new DeferredCancellation();

        /** @var \Amp\Future[] $futures */
        $futures = [];
        foreach ($this->commands as $cmd) {
            // Run command in parallel
            // If any command is handled, cancel the rest
            $futures[] = async(function (Command $cmd) use (&$handled, $cancellation) {
                $handled = $this->safeRunCommand($cmd);
                if ($handled) {
                    $cancellation->cancel();
                }
            }, $cmd);
        }

        try {
            await($futures, $cancellation->getCancellation());
        } catch (Throwable) {
            return;
        }

        if ($handled === false) {
            $this->fallbackCommand?->setCommands($this->commands);
            $this->fallbackCommand?->handle($this->api(), $this->ctx());
        }
    }

    private function safeRunCommand(Command $cmd): bool
    {
        try {
            return $this->runCommand($cmd);
        } catch (Throwable $e) {
            if ($this->bot->handleException($e, $this->bot, $this->ctx())) {
                return true;
            }

            $this->logger()->error('Fail to run command {name} ({eventType}), reason: {reason} on {file}:{line}', [
                'name' => $cmd::class,
                'eventType' => $cmd->type()->prettyName(),
                'reason' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => $e,
            ]);
            return false;
        }
    }

    private function runCommand(Command $cmd): bool
    {
        $cloneCmd = clone $cmd;
        $cloneCmd->setVars($this->api(), $this->ctx())
            ->setDb($this->db());

        if (!$cloneCmd->isValid()) {
            return false;
        }

        // Cant validate filter but method onInvalidFilter return true
        if (!$cloneCmd->validateFilters()) {
            $executed = $cloneCmd->onInvalidFilters();
            // null = not execute
            // false = execute, but cant continue
            // true = execute, and continue
            if ($executed === null) {
                return false;
            }
            if ($executed === false) {
                return true;
            }
        }

        $nextEvent = $cloneCmd->setLogger($this->logger())->execute(
            $this->bot->handleMiddlewares($cloneCmd, $this->ctx(), $this->logger())
        );

        $this->getLogger()->debug('Command {name} ({eventType}) executed', [
            'name' => $cloneCmd::class,
            'eventType' => $cloneCmd->type()->prettyName(),
        ]);

        // Delete temporary event
        if ($cloneCmd instanceof TemporaryEvent) {
            $this->bot->deleteEvent($cloneCmd);
        }

        // Register next conversation
        if ($nextEvent instanceof Conversation) {
            $this->bot->registerConversation(
                $nextEvent->setVars($this->botApi, clone $this->botContext)
                    ->setDb($this->db())
            );
        }

        return true;
    }

    public function add(Command $command): static
    {
        $this->logger()->debug('Register command {name} ({eventType})', [
            'name' => $command::class,
            'eventType' => $command->type()->prettyName(),
        ]);
        $this->commands[$command->getName()] = $command;
        return $this;
    }

    public function withDefaultFallbackCommand(): static
    {
        $this->setFallbackCallable(function (Bot $bot, Context $ctx, array $cmds) {
        });
        return $this;
    }

    /**
     * @param Closure $fallbackCommand (Bot, Context, Commands[])
     * @return static
     */
    public function setFallbackCallable(Closure $fallbackCommand): static
    {
        return $this->setFallbackCommand(new class ($fallbackCommand) implements FallbackCommand {
            private array $cmds = [];

            public function __construct(private readonly Closure $fallbackCommand)
            {
            }

            public function handle(Api $bot, Context $context): void
            {
                EventLoop::queue($this->fallbackCommand, $bot, $context, $this->commands());
            }

            public function commands(): array
            {
                return $this->cmds;
            }

            public function setCommands(array $commands): static
            {
                $this->cmds = $commands;
                return $this;
            }
        });
    }

    /**
     * @param FallbackCommand $command Command to execute when you cannot validate the other commands
     */
    public function setFallbackCommand(FallbackCommand $command): static
    {
        $this->fallbackCommand = $command;
        return $this;
    }

    /**
     * @api
     */
    public function setBot(Bot $bot): static
    {
        $this->bot = $bot;
        return $this;
    }
}
