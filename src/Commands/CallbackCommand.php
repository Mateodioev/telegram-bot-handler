<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\EventType;

abstract class CallbackCommand extends Command
{
    public EventType $type = EventType::callback_query;

    /**
     * @inheritDoc
     */
    protected function buildRegex(): string
    {
        $format = '#^(%s)(?: .+)?$#';
        $alias = [$this->getName(), ...$this->getAliases()];

        return sprintf($format,
            join('|', $alias)
        );
    }

    /**
     * @inheritDoc
     */
    public function match(string $text): bool
    {
        return (bool) preg_match($this->buildRegex(), $text);
    }

    public function isValid(Api $bot, Context $ctx): bool
    {
        $query = $ctx->getMessageText() ?? '';

		if (empty($query)) return false;
		
		return $this->match($query);
    }

    public function execute(Api $bot, Context $context, array $args = [])
    {
        $query = $context->getMessageText() ?? '';

		if (empty($query)) return;
		
		if ($this->match($query)) {
			$this->handle($bot, $context, $args);
		}
    }

	/**
	 * Run command
     * @param Api $bot Telegram bot api
     * @param Context $context Telegram context / update
     * @param array $args Middleware results
	 */
	abstract public function handle(Api $bot, Context $context, array $args = []);
}