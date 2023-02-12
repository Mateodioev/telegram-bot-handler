<?php

namespace Mateodioev\TgHandler\Log;

class TerminalStream implements Stream
{

    public function push(string $message): void
    {
        print($message);
    }
}