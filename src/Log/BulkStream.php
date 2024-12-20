<?php

declare (strict_types=1);

namespace Mateodioev\TgHandler\Log;

use Revolt\EventLoop;
use SimpleLogger\streams\LogResult;

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

    public function push(LogResult $message, ?string $level = null): void
    {
        array_walk(
            $this->streams,
            function (Stream $stream) use ($message, $level): void {
                EventLoop::defer(fn () => $stream->push($message, $level));
            }
        );
    }
}
