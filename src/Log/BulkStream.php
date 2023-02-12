<?php

namespace Mateodioev\TgHandler\Log;

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
        foreach (self::$streams as $stream) {
            $stream->push($message);
        }
    }
}