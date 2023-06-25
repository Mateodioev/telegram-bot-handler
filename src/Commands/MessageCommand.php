<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\StringVars\Config;
use Mateodioev\TgHandler\Context;
use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Events\EventType;

abstract class MessageCommand extends Command
{
    public EventType $type = EventType::message;

    /**
     * @var string[] Prefix of commands
     */
    protected array $prefix = ['/'];

    /**
     * Regex param matchers
     * Example:
     * ```php
     * $params = '{name}';
     * ```
     */
    protected string $params = '{all:payload}?';

    private ?Matcher $pattern = null;
    private array $commandParams = [];

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
     * @see https://github.com/Mateodioev/string-vars
     */
    public function setParams(string $pattern = '{all:payload}?'): static
    {
        $this->params = $pattern;
        return $this;
    }

    /**
     * Get params string format
     */
    public function params(): string
    {
        return $this->params;
    }

    /**
     * Get params from text
     */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->commandParams[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    protected function buildRegex(): Matcher
    {
        if ($this->pattern instanceof Matcher)
            return $this->pattern;

        // prefix names parameters
        $format = '(?:%s)(?:%s)( %s)?';
        $alias = [$this->getName(), ...$this->getAliases()];

        $pattern = sprintf(
            $format,
            str_replace('#', '\#', join('|', $this->getPrefix())),
            // for commands like #start
            join('|', $alias),
            $this->params()
        );

        $this->pattern = new Matcher($pattern);
        return $this->pattern;
    }

    /**
     * @inheritDoc
     */
    public function match(string $text): bool
    {
        $isValid = $this->buildRegex()->isValid($text, true);
        if ($isValid)
            $this->commandParams = $this->pattern->match($text);

        return $isValid;
    }

    public function isValid(Api $bot, Context $ctx): bool
    {
        return 1 === 1 // SQL format
            && !empty($ctx->getMessageText())
            && $this->match($ctx->getMessageText());
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