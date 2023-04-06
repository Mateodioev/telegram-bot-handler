<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\EventType;

abstract class MessageCommand extends Command
{
    public EventType $type = EventType::message;

    protected bool $caseSensitive = false;

    /**
     * @var string[] Prefix of commands
     */
    protected array $prefix = ['/'];

    /**
     * @return array
     */
    public function getPrefix(): array
    {
        return $this->prefix;
    }

    /**
     * Add an element to prefix array
     */
    public function addPrefix(string $prefix): static
    {
        $this->prefix[] = $prefix;
        return $this;
    }

    public function setPrefixes(array $prefixes): static
    {
        $this->prefix = $prefixes;
        return $this;
    }

    /**
     * Enable or disable case sensitive commands
     */
    public function setCaseSensitive(bool $caseSensitive = false): static
    {
        $this->caseSensitive = $caseSensitive;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function buildRegex(): string
    {
        $format = '#^(%s)(%s)(?: .*)?$#' . ($this->caseSensitive ? 'i' : '');
        $alias = [$this->getName(), ...$this->getAliases()];

        return sprintf($format,
            str_replace('#', '\#', join('|', $this->getPrefix())), // for commands like #start
            join('|', $alias)
        );
    }

    /**
     * @inheritDoc
     */
    public function match(string $text): bool
    {
        return preg_match($this->buildRegex(), $text) > 0;
    }

    public function isValid(Api $bot, Context $ctx): bool
    {
        $text = $ctx->getMessageText();

        if (empty($text)) return false;

        return $this->match($text);
    }

    public function execute(Api $bot, Context $context, array $args = [])
    {
        $this->handle($bot, $context, $args);
    }

	/**
	 * Run command
     * @param Api $bot Telegram bot api
     * @param Context $context Telegram context / Update
     * @param array $args Middlewares results
	 */
    abstract public function handle(Api $bot, Context $context, array $args = []);
}