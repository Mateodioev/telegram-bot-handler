<?php

namespace Mateodioev\TgHandler\Log;

use function array_walk;

/**
 * Collections of streams
 */
class BulkStream implements Stream
{
    /**
     * @var Stream[]
     */
    public array $streams = [];

    public function __construct(Stream ...$streams)
    {
        $this->streams = $streams;
    }

    /**
     * Add new stream
     */
    public function add(Stream $stream): static
    {
        $this->streams[] = $stream;
        return $this;
    }

    public function push(string $message, ?string $level = null): void
    {
        array_walk(
            $this->streams,
            fn(Stream $stream) => $stream->push($message)
        );
    }
}
