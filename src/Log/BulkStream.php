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
    public static array $streams;

    /**
     * Add new stream
     */
    public static function add(Stream $stream): void
    {
        self::$streams[] = $stream;
    }

    public function push(string $message): void
    {
        array_walk(
            self::$streams,
            fn(Stream $stream) => $stream->push($message)
        );
    }
}