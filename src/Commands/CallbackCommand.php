<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Events\{EventType};

abstract class CallbackCommand extends Command
{
    public EventType $type = EventType::callback_query;

    private ?Matcher $pattern = null;

    /**
     * @inheritDoc
     */
    protected function buildRegex(): Matcher
    {
        if ($this->pattern instanceof Matcher) {
            return $this->pattern;
        }

        $format = '(%s)(?: .+)?';
        $alias = [$this->getName(), ...$this->getAliases()];

        $this->pattern = new Matcher(
            sprintf(
                $format,
                join('|', $alias)
            )
        );

        /** @var Matcher $this->pattern */
        return $this->pattern;
    }

    /**
     * @inheritDoc
     */
    protected function match(string $text): bool
    {
        return $this->buildRegex()->isValid($text, true);
    }

    public function isValid(): bool
    {
        return 1 === 1 // SQL format
            && parent::isValid()
            && !empty($this->ctx()->getMessageText())
            && $this->match($this->ctx()->getMessageText());
    }
}
