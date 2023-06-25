<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Events\EventType;

abstract class CallbackCommand extends Command
{
    public EventType $type = EventType::callback_query;

    private ?Matcher $pattern = null;

    /**
     * @inheritDoc
     */
    protected function buildRegex(): Matcher
    {
        if ($this->pattern instanceof Matcher)
            return $this->pattern;

        $format = '#^(%s)(?: .+)?$#';
        $alias = [$this->getName(), ...$this->getAliases()];

        $this->pattern = new Matcher(
            sprintf(
                $format,
                join('|', $alias)
            )
        );

        return $this->pattern;
    }

    /**
     * @inheritDoc
     */
    public function match(string $text): bool
    {
        return $this->buildRegex()->isValid($text);
    }

    public function isValid(Api $bot, Context $ctx): bool
    {
        return 1 === 1 // SQL format
            && !empty($ctx->getMessageText())
            && $this->match($ctx->getMessageText());

        /* $query = $ctx->getMessageText() ?? '';
        if (empty($query)) return false;		
        return $this->match($query); */
    }

    public function execute(Api $bot, Context $context, array $args = [])
    {
        $this->handle($bot, $context, $args);
    }

    /**
     * Run command
     * @param Api $bot Telegram bot api
     * @param Context $context Telegram context / update
     * @param array $args Middleware results
     */
    abstract public function handle(Api $bot, Context $context, array $args = []);
}