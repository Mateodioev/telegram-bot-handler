<?php

namespace Mateodioev\TgHandler\Commands\Generics;

use Closure;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\Command;
use Mateodioev\TgHandler\Conversations\Conversation;
use Mateodioev\TgHandler\Events\{TemporaryEvent, abstractEvent};
use Mateodioev\TgHandler\{Bot, Context};
use Revolt\EventLoop;
use Throwable;

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

    public function execute($args = [])
    {
        $this->fallbackCommand?->setCommands($this->commands);
        $handled = false; // If any command is handled set to true

        foreach ($this->commands as $cmd) {
            try {
                $handled = $this->runCommand($cmd);
            } catch (Throwable $e) {
                if ($this->bot->handleException($e, $this->bot, $this->ctx())) {
                    return;
                }

                $this->logger()->error('Fail to run command {name} ({eventType}), reason: {reason} on {file}:{line}', [
                    'name'      => $cmd::class,
                    'eventType' => $cmd->type()->prettyName(),
                    'reason'    => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine()
                ]);
            }
        }

        if ($handled === false) {
            $this->fallbackCommand?->handle($this->api(), $this->ctx());
        }
    }

    private function runCommand(Command $cmd): bool
    {
        $cmd->setVars($this->api(), $this->ctx())
            ->setDb($this->db());

        if (!$cmd->isValid()) {
            return false;
        }

        if (!$cmd->validateFilters()) {
            return $cmd->onInvalidFilters();
        }

        $return = $cmd->setLogger($this->logger())->execute(
            $this->bot->handleMiddlewares($cmd, $this->ctx())
        );

        // Delete temporary event
        if ($cmd instanceof TemporaryEvent) {
            $this->bot->deleteEvent($cmd);
        }

        // Register next conversation
        if ($return instanceof Conversation) {
            $this->bot->onEvent($return);
        }

        return true;
    }

    public function add(Command $command): static
    {
        $this->logger()->debug('Register command {name} ({eventType})', [
            'name'      => $command::class,
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
     * @param Closure(Bot, Context, Commands[]) $name
     * @return static
     */
    public function setFallbackCallable(Closure $fallbackCommand): static
    {
        return $this->setFallbackCommand(new class ($fallbackCommand) implements FallbackCommand {
            private array $cmds = [];

            public function __construct(private Closure $fallbackCommand)
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

    public function setBot(Bot $bot): static
    {
        $this->bot = $bot;
        return $this;
    }
}
