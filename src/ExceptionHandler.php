<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler;

use Closure;
use Mateodioev\TgHandler\Commands\StopCommand;
use Psr\Log\LoggerInterface;
use Throwable;

use function call_user_func;
use function is_a;

class ExceptionHandler
{
    /**
     * @var array<string, Closure>
     */
    private array $handlers = [];

    public function __construct(public ?LoggerInterface $logger = null)
    {
    }

    public function add(string $name, Closure $handler): static
    {
        $this->logger?->info('Register exception handler for {exceptionName}', [
            'exceptionName' => $name,
        ]);
        $this->handlers[$name] = $handler;
        return $this;
    }

    /**
     * Find the handler for the given exception.
     * @param Throwable $exception
     */
    private function find(Throwable $exception): ?Closure
    {
        $exceptionName = $exception::class;
        $handler = $this->handlers[$exceptionName] ?? null;

        if ($handler !== null) {
            return $handler;
        }

        foreach ($this->handlers as $name => $exceptionHandler) {
            if (is_a($exception, $name)) {
                return $exceptionHandler;
            }
        }

        return null;
    }

    /**
     * Handle the exception.
     * @return bool True if the exception was handled, false otherwise.
     */
    public function handle(Throwable $e, Bot $bot, Context $ctx): bool
    {
        $handler = $this->find($e);

        if ($handler === null) {
            return false;
        }

        call_user_func($handler, $e, $bot, $ctx);
        // Ignore StopCommand exceptions bc is used to stop the bot
        if ($e::class === StopCommand::class) {
            return true;
        }
        $this->logger?->info('Exception "{e}" handled', ['e' => $e::class]);
        return true;
    }

    /**
     * Handler use by amphp
     * @deprecated v5.9.3
     */
    public function toEventLoopHandler(): Closure
    {
        return function (Throwable $exception) {
            $handler = $this->find($exception);
            if ($handler === null) {
                return;
            }

            $handler($exception);
            $this->logger?->info('EventLoop Exception "{e}" handled', ['e' => $exception::class]);
        };
    }
}
