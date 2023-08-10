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

        $this->stdout = $stdout;
    }

    public function push(string $message, ?string $level = null): void
    {
        fwrite($this->stdout, $message);
    }
}
