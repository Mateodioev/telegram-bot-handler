<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\TgHandler\Events\EventInterface;

/**
 * Handle an incoming command
 */
interface CommandInterface extends EventInterface
{
    /**
     * The name of the telegram command
     */
    public function getName(): string;

    /**
     * Command aliases
     */
    public function getAliases(): array;
}
