<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Events\{EventType};

use function join;
use function sprintf;
use function str_replace;

abstract class MessageCommand extends Command
{
    public const DEFAULT_PARAMS = '{all:payload}?';
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
    protected string $params = self::DEFAULT_PARAMS;

    /**
     * @var Matcher|null Regex used to match the input
     */
    protected ?Matcher $pattern = null;

    /**
     * @var array Contains the
     */
    protected array $commandParams = [];

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

    protected function buildRegex(): Matcher
    {
        if ($this->pattern instanceof Matcher) {
            return $this->pattern;
        }

        // prefix names parameters
        $format = '(?:%s)(?:%s)%s';

        $alias = [$this->getName(), ...$this->getAliases()];
        // For commands like #start
        $prefixes = str_replace(
            '#',
            '\#',
            join('|', $this->getPrefix()),
        );
        // if params was not set, optional payload are allowed
        $paramsMatcher = $this->params() === self::DEFAULT_PARAMS
            ? '( ' . self::DEFAULT_PARAMS . ')?'
            : ' ' . $this->params();

        $pattern = sprintf(
            $format,
            $prefixes,
            join('|', $alias),
            $paramsMatcher
        );

        $this->pattern = new Matcher($pattern);
        return $this->pattern;
    }

    protected function match(string $text): bool
    {
        $isValid = $this->buildRegex()->isValid($text, true);
        if ($isValid) {
            $this->commandParams = $this->pattern->match($text);
        }

        return $isValid;
    }

    public function isValid(): bool
    {
        return parent::isValid()
            && !empty($this->ctx()->getMessageText())
            && $this->match($this->ctx()->getMessageText());
    }
}
