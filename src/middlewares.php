<?php

namespace Mateodioev\TgHandler;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\CommandInterface;
use Closure, Exception;
use Mateodioev\TgHandler\Commands\StopCommand;

trait middlewares
{
    /**
     * @throws Exception
     * @return array Returns array of middlewares results, only include results that are not null
     */
    protected function handleMiddlewares(CommandInterface $command, Context $context): array
    {
        if (!$command->hasMiddlewares()) // Check if command has middlewares
            return [];

        $middlewares = $command->middlewares();

        $params = array_map(fn($middleware) => $this->runMiddleware($middleware, $context), $middlewares);
        return array_filter($params, fn($param) => $param !== null);
    }

    /**
     * @throws Exception
     */
    protected function runMiddleware($middleware, Context $context): mixed
    {
        try {
            return call_user_func($middleware, $context, $this->getApi());
        } catch (StopCommand $e) {
            throw $e;
        } catch (Exception $e) {
            if (!$this->handleException($e, $this, $context)) {
                throw $e;
            }
            return null;
        }
    }
}
