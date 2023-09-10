<?php

namespace Mateodioev\TgHandler\Log;

use function fwrite;

final class ResourceStream implements Stream
{
    /**
     * @param resource $stream
     */
    public function __construct(
        private $stream
    ) {
    }

    public function push(string $message, ?string $level = null): void
    {
        fwrite($this->stream, $message);
    }

    public function clear(): void
    {
        \rewind($this->stream);
        \ftruncate($this->stream, 0);
    }

    public function close(): void
    {
        \fclose($this->stream);
    }
}
