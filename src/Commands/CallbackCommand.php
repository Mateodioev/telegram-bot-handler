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
        $format = '#^(%s)((?: .+)?$#';
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
        return preg_match($this->buildRegex(), $text) > 1;
    }

    public function execute(Api $bot, Context $context)
    {
        $query = $context->getCallbackQueryText();

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