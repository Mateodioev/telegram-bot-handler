<?php

namespace Mateodioev\TgHandler\Log;

use function fwrite;

final class TerminalStream implements Stream
{
    private $stdout;

    public function __construct($stdout = STDOUT)
    {
        if (!$stdout)
            $stdout = STDOUT;

        $this->stdout = new ResourceStream($stdout);
    }

    public function push(string $message, ?string $level = null): void
    {
        $this->stdout->push($message, $level);
    }
}
