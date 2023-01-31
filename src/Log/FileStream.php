<?php

namespace Mateodioev\TgHandler\Log;

use Mateodioev\Bots\Telegram\Exception\InvalidFileException;

class FileStream implements Stream
{
    protected $file;

    public function __construct(string $fileName)
    {
        $this->file = fopen($fileName, 'a');

        if ($this->file === false) {
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
        fwrite($this->file, $message);
    }

    public function __destruct()
    {
        fclose($this->file);
    }
}