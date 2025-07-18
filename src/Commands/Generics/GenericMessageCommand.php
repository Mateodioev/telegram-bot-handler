<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands\Generics;

use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Events\EventType;

use function in_array;
use function strlen;

class GenericMessageCommand extends GenericCommand
{
    public EventType $type = EventType::message;

    /**
     * Use default message command fallback
     */
    public function withDefaultFallbackCommand(): static
    {
        return $this->setFallbackCommand(new FallbackMessageCommand());
    }

    public function isValid(): bool
    {
        return parent::isValid()
            && !empty($txt = $this->ctx()->getMessageText())
            && strlen($txt) > 1
            && in_array($txt[0], $this->getPrefix());
    }

    private function getPrefix()
    {
        $prefixes = [];
        foreach ($this->commands as $cmd) {
            if ($cmd instanceof MessageCommand) {
                foreach ($cmd->getPrefix() as $prefix) {
                    $prefixes[] = $prefix;
                }
            }
        }
        return $prefixes;
    }
}
