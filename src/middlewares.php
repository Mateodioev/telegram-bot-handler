<?php

namespace Mateodioev\TgHandler;

use Exception;
use Mateodioev\TgHandler\Commands\StopCommand;
use Mateodioev\TgHandler\Events\EventInterface;

use function array_filter;
use function array_map;
use function call_user_func;

trait middlewares
{
    /**
     * @throws Exception
     * @return array Returns array of middlewares results, only include results that are not null
     */
    protected function handleMiddlewares(EventInterface $event, Context $context): array
    {
        if (!$event->hasMiddlewares()) { // Check if command has middlewares
            return [];
        }

        $middlewares = $event->middlewares();

        $params = array_map(fn ($middleware) => $this->runMiddleware($middleware, $context), $middlewares);
        return array_filter($params, fn ($param) => $param !== null);
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
