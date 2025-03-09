<?php

declare (strict_types=1);

namespace Tests\Events;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\Types\AllEvent;
use Mateodioev\TgHandler\Middleware\{ClosureMiddleware, Middleware};
use Monolog\Test\TestCase;

class MiddlewareTest extends TestCase
{
    public function testCreateMiddleware()
    {
        $middleware = $this->createMiddleware();
        $this->assertInstanceOf(Middleware::class, $middleware);
    }

    public function testCreateMiddlewareFromClosure()
    {
        $middleware = ClosureMiddleware::create(function () {
            return 'middleware from closure :)';
        });

        $this->assertInstanceOf(Middleware::class, $middleware);
        $this->assertIsCallable($middleware);
        $this->assertNotEmpty((string) $middleware);
        $this->assertNotEmpty($middleware->name());
        $this->assertIsCallable($middleware->callable);
    }

    public function testIsMiddlewareCallable()
    {
        $this->assertIsCallable($this->createMiddleware());
    }

    public function testGetMiddlewareName()
    {
        $middleware = $this->createMiddleware();
        $this->assertIsString($middleware->name());
        $this->assertIsString((string) $middleware);
        $this->assertTrue($middleware->name() === (string) $middleware);
        $this->assertNotEmpty($middleware->name());
    }

    public function testSetEventMiddleware()
    {
        $event = $this->createEvent();
        // Add some middlewares
        $event->addMiddleware($this->createMiddleware());
        $event->addMiddleware($this->createMiddleware());

        $this->assertNotEmpty($event->middlewares());
        $this->assertCount(2, $event->middlewares());
        $this->assertIsArray($event->middlewares());
    }

    public function testGetEventMiddleware()
    {
        $event = $this->createEvent();
        $event->addMiddleware($this->createMiddleware());
        $event->addMiddleware($this->createMiddleware());

        $this->assertIsArray($event->middlewares());
        foreach ($event->middlewares() as $i => $middleware) {
            $this->assertIsString($i);
            $this->assertIsObject($middleware);
            $this->assertInstanceOf(Middleware::class, $middleware);
        }
    }

    public function testMiddlewareWithParams()
    {
        $middleware = $this->createMiddlewareWithParams();
        $this->assertIsString($middleware->name());
        $this->assertEquals('param', $middleware->name());

        $event = $this->createEvent();
        $event->addMiddleware($middleware);

        $this->assertNotEmpty($event->middlewares());
        $this->assertCount(1, $event->middlewares());
        $this->assertIsArray($event->middlewares());

        $this->assertIsString($event->middlewares()['param']->name());
        $this->assertEquals('param', $event->middlewares()['param']->name());
    }

    private function createMiddleware(): Middleware
    {
        return new class () extends Middleware {
            public function name(): string
            {
                return '#' . spl_object_id($this); // random name for testing
            }

            public function __invoke(Context $ctx, Api $api, array $args = []): string
            {
                return 'middleware';
            }
        };
    }

    private function createEvent(): AllEvent
    {
        return new class () extends AllEvent {
            public function execute(array $args = [])
            {
                // TODO: Implement execute() method.
            }
        };
    }

    private function createMiddlewareWithParams(): Middleware
    {
        return new class ('param') extends Middleware {
            public function __construct(
                private readonly string $param
            ) {
            }

            public function __invoke(Context $ctx, Api $api, array $args = []): string
            {
                return $this->param;
            }

            public function name(): string
            {
                return $this->param; // Use the param passed
            }
        };
    }
}
