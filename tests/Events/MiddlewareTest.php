<?php

declare(strict_types=1);

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
        $middleware = ClosureMiddleware::create(function (Context $ctx, Api $api) {
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
        $this->assertEquals(2, count($event->middlewares()));
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

    private function createMiddleware()
    {
        return new class () extends Middleware {
            public function name(): string
            {
                return '#' . spl_object_id($this); // random name for testing
            }

            public function __invoke(Context $context, Api $api): mixed
            {
                return 'middleware';
            }
        };
    }

    private function createEvent()
    {
        return new class () extends AllEvent {
            public function execute(array $args = [])
            {
                // TODO: Implement execute() method.
            }
        };
    }
}
