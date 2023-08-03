<?php

namespace Mateodioev\TgHandler\Log;

final class TerminalStream implements Stream
{

    public function push(string $message, ?string $level = null): void
    {
        print($message);
    }
}