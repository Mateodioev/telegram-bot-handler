<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

abstract class CallbackCommand extends Command
{
    protected string $type = 'callback';

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

    public function execute(Api $bot, Context $context)
    {
        $query = $context->getMessageText() ?? '';

		if (empty($query)) return;
		
		if ($this->match($query)) {
			$this->handle($bot, $context);
		}
    }

	/**
	 * Run command
	 */
	abstract public function handle(Api $bot, Context $context);
}