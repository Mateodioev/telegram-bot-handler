<?php

declare (strict_types=1);

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\StringVars\{Config, Matcher};

use function join;

/**
 * This command is similar to {@see MessageCommand} but allows any string in the command
 *
 * Example valid command:
 * ```text
 * /startANY_STRING_HERE Hello
 * ```
 *
 * This is useful if you want to put a secret token in the command and not in the args.
 * Only
 *
 * @api
 */
abstract class DynamicMessageCommand extends MessageCommand
{
    /**
     * @var ?string Token in the command
     */
    private ?string $commandToken = null;

    /**
     * @return string Get the token in the command
     * @api
     */
    protected function commandToken(): String
    {
        if ($this->commandToken !== null) {
            return $this->commandToken;
        }
        $this->commandToken = $this->commandParams['xxcommandToken'];
        return $this->commandToken;
    }

    protected function buildRegex(): Matcher
    {
        if ($this->pattern instanceof Matcher) {
            return $this->pattern;
        }

        $regexFormat = '(?:%s)(?:%s){x:xxcommandToken}%s';

        $alias = [$this->getName(), ...$this->getAliases()];
        $prefixes = str_replace('#', '\#', join('|', $this->getPrefix()));
        $paramsMatcher = $this->params() === static::DEFAULT_PARAMS
        ? '( ' . static::DEFAULT_PARAMS . ')?'
        : ' ' . $this->params();

        $pattern = sprintf(
            $regexFormat,
            $prefixes,
            join('|', $alias),
            $paramsMatcher,
        );

        return $this->pattern = new Matcher(
            $pattern,
            $this->getMatcherConfig(),
        );
    }

    private function getMatcherConfig(): Config
    {
        return new Config([
            'x' => '([\S]+)',
        ]);
    }
}
