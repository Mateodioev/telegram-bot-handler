<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use Amp\ByteStream\{ClosedException, StreamException};
use Amp\File\{File, FilesystemException};
use Mateodioev\Bots\Telegram\Exception\InvalidFileException;
use SimpleLogger\Formatters\{DefaultFormatter, Formatter};
use SimpleLogger\streams\LogResult;

use function date;
use function realpath;

/**
 * Write logs to a file
 */
class FileStream implements Stream
{
    public const OPEN_MODE = 'a';

    protected File $file;
    private Formatter $formatter;

    /**
     * @throws FilesystemException
     */
    public function __construct(string $fileName)
    {
        $this->file = \Amp\File\openFile($fileName, self::OPEN_MODE);
        // $this->file = fopen($fileName, 'a');

        if (!$this->file->isWritable()) {
            throw new InvalidFileException('File ' . $fileName . ' is not writable');
        }

        $this->formatter = new DefaultFormatter();
    }

    /**
     * Create a new file stream with today's date
     * @throws FilesystemException
     */
    public static function fromToday(string $dir): FileStream
    {
        $fileName = date('Y-m-d') . '-php_error.log';
        return new static(realpath($dir) . '/' . $fileName);
    }

    /**
     * @throws ClosedException
     * @throws StreamException
     */
    public function push(LogResult $message, ?string $level = null): void
    {
        $this->file->write($this->formatter->format($message));
    }

    /**
     * @throws StreamException
     * @throws ClosedException
     */
    public function __destruct()
    {
        $this->file->end();
        $this->file->close();
    }
}
