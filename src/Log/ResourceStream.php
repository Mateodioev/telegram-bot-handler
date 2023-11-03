<?php

namespace Mateodioev\TgHandler\Log;

use InvalidArgumentException;

use function fclose;
use function ftruncate;
use function fwrite;
use function rewind;

/**
 * Send logs to a resource
 */
final class ResourceStream implements Stream
{
    /**
     * @param resource $stream
     * @throws InvalidArgumentException if $stream is not a resource
     */
    public function __construct(
        private $stream
    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('stream must be a resource');
        }
    }

    public function push(string $message, ?string $level = null): void
    {
        fwrite($this->stream, $message);
    }

    public function clear(): void
    {
        rewind($this->stream);
        ftruncate($this->stream, 0);
    }

    public function close(): void
    {
        fclose($this->stream);
    }
}
