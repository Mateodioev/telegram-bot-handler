<?php

namespace Mateodioev\TgHandler\Log;

use Mateodioev\Bots\Telegram\Exception\InvalidFileException;

class FileStream implements Stream
{
    const OPEN_MODE = 'a';

    protected $file;

    public function __construct(string $fileName)
    {
        $this->file = \Amp\File\openFile($fileName, self::OPEN_MODE);
        // $this->file = fopen($fileName, 'a');

        if (!$this->file->isWritable()) {
            throw new InvalidFileException('File ' . $fileName . ' is not writable');
        }
    }

    public static function fromToday(string $dir): FileStream
    {
        $fileName = date('Y-m-d') . '-php_error.log';
        return new static(realpath($dir) . '/' . $fileName);
    }

    public function push(string $message): void
    {
        $this->file->write($message);
        // fwrite($this->file, $message);
    }

    public function __destruct()
    {
        $this->file->end();
        $this->file->close();
    }
}