<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use Amp\ByteStream\{WritableResourceStream, WritableStream};
use InvalidArgumentException;
use SimpleLogger\Formatters\{DefaultFormatter, Formatter};
use SimpleLogger\streams\LogResult;

/**
 * Send logs to a resource
 */
final class ResourceStream implements Stream
{
    private Formatter $formatter;
    private WritableStream $stream;

    /**
     * @param resource $stream
     * @throws InvalidArgumentException if $stream is not a resource
     */
    public function __construct(
        $stream,
        ?Formatter $formatter = null,
    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('stream must be a resource');
        }
        $this->stream = new WritableResourceStream($stream);
        $this->formatter = $formatter ?? new DefaultFormatter();
    }

    public function push(LogResult $message, ?string $level = null): void
    {
        $this->stream->write($this->formatter->format($message));
    }

    public function close(): void
    {
        $this->stream->end();
    }
}
