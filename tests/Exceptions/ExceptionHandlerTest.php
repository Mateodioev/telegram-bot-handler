<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Mateodioev\TgHandler\{Context, ExceptionHandler};
use Monolog\Test\TestCase;
use Tests\EmptyBot;
use Throwable;

class ExceptionHandlerTest extends TestCase
{
    public function testHandleException(): void
    {
        $handler = new ExceptionHandler();

        $handler->add(CustomException::class, self::customHandler(...));

        $handler->handle(new CustomException(), new EmptyBot(), new Context());
    }

    public function testHandleExceptionWithErrorMessage(): void
    {
        $handler = new ExceptionHandler();

        $handler->add(CustomException::class, self::customHandlerWithErrorMessage(...));

        $handler->handle(new CustomException('Test message'), new EmptyBot(), new Context());
    }

    /**
     * This exception only its called when the handler match the exception
     *
     * @param class-string $exception
     */
    private function customHandler(Throwable $exception)
    {
        $this->assertTrue(true);
        $this->assertEquals($exception::class, CustomException::class);
    }

    private function customHandlerWithErrorMessage(Throwable $exception)
    {
        $this->assertEquals($exception->getMessage(), 'Test message');
    }
}
