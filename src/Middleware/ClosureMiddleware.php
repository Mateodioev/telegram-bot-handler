<?php

namespace Mateodioev\TgHandler\Middleware;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

use function call_user_func;
use function spl_object_id;

/**
 * Create a middleware from a closure
 */
class ClosureMiddleware extends Middleware
{
    /**
     * @var int Middleware ID
     */
    public int $id;

    /**
     * @param callable $callable
     */
    private function __construct(public $callable)
    {
        $this->id = spl_object_id($this);
    }

    /**
     * @param callable $callable
     */
    public static function create($callable): static
    {
        return new static($callable);
    }

    /**
     * Set a custom id for this middleware
     */
    public function withId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function name(): string
    {
        return $this->id;
    }

    public function __invoke(Context $ctx, Api $api)
    {
        return call_user_func($this->callable, $ctx, $api);
    }
}
