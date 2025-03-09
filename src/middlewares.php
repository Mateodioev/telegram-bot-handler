<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler;

use Exception;
use Mateodioev\TgHandler\Commands\StopCommand;
use Mateodioev\TgHandler\Events\EventInterface;
use Mateodioev\TgHandler\Middleware\Middleware;
use Psr\Log\LoggerInterface;

use function array_filter;

trait middlewares
{
    /**
     * @throws Exception
     * @return array Returns array of middlewares results, only include results that are not null
     */
    public function handleMiddlewares(EventInterface $event, Context $context, LoggerInterface $logger): array
    {
        if (!$event->hasMiddlewares()) { // Check if command has middlewares
            return [];
        }
        $middlewares = $event->middlewares();

        $params = [];
        foreach ($middlewares as $middleware) {
            $middleware->setLogger($logger);
            $params[$middleware->name()] = $this->runMiddleware($middleware, $context, $params);
        }

        // Delete empty outputs
        return array_filter($params, fn ($param) => $param !== null);
    }

    /**
     * @throws Exception
     */
    protected function runMiddleware(Middleware $middleware, Context $context, array $previousResults): mixed
    {
        try {
            return $middleware($context, $this->getApi(), $previousResults);
        } catch (StopCommand $e) {
            // This need to be handled by StopCommand::handler
            throw $e;
        } catch (Exception $e) {
            if (!$this->handleException($e, $this, $context)) {
                throw $e;
            }
            return null;
        }
    }
}
