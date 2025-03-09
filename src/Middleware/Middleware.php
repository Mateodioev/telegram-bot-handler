<?php

namespace Mateodioev\TgHandler\Middleware;

use Exception;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\StopCommand;
use Mateodioev\TgHandler\Context;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Base class for middlewares
 */
abstract class Middleware implements Stringable
{
    /**
     * @var string|null Name of the middleware
     */
    protected ?string $name = null;
    private LoggerInterface $logger;

    /**
     * Name of the middleware
     */
    public function name(): string
    {
        return $this->name ??= static::class;
    }

    /**
     * @param array<string, mixed> $args Results of the previous middlewares execution
     * @throws StopCommand to stop the current command execution
     * @throws Exception
     */
    abstract public function __invoke(Context $ctx, Api $api, array $args = []);

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->name();
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }
}
