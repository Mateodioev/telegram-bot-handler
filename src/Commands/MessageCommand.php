<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

abstract class MessageCommand extends Command
{
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
            join('|', $this->getPrefix()),
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

    public function execute(Api $bot, Context $context, array $args = [])
    {
        $text = $context->getMessageText();

        if (empty($text)) return;

        if ($this->match($text)) {
            $this->handle($bot, $context, $args);
        }
    }

	/**
	 * Run command
     * @param Api $bot Telegram bot api
     * @param Context $context Telegram context / Update
     * @param array $args Middlewares results
	 */
    abstract public function handle(Api $bot, Context $context, array $args = []);
}