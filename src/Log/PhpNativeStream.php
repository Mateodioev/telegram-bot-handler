<?php

namespace Mateodioev\TgHandler\Log;

use Amp\File;
use Amp\File\FilesystemException;
use Mateodioev\Utils\Exceptions\FileException;
use Mateodioev\Utils\Files;

use function is_dir, date, error_reporting, ini_set, set_error_handler, fclose, fopen, restore_error_handler, sprintf;

/**
 * Log php errors into a file setting an error_handler
 */
final class PhpNativeStream implements Stream
{
    public string $fileLog;

    public function activate(string $dir, ?string $file = null): PhpNativeStream
    {
        if (is_dir($dir) && $file !== null)
            throw new FileException('Invalid dir');

        if ($file !== null) {
            if (!Files::isFile($file)) {
                throw new FileException('Invalid file');
            } else {
                $this->setFile($file);
            }
        } else {
            $this->setFile($dir . '/' . date('Y-m-d') . '-php_error.log');
        }

        error_reporting(E_ALL);
        ini_set('display_errors', false);
        ini_set('log_errors', true);
        ini_set('error_log', $this->fileLog);

        set_error_handler($this->errorHandler(...));

        return $this;
    }

    public function setFile(string $path): PhpNativeStream
    {
        $this->fileLog = $path;

        if (!Files::isFile($path)) {
            fclose(fopen($path, 'a')); // create file if not exists
        }

        return $this;
    }

    public function deactivate(): void
    {
        restore_error_handler();
    }

    public function errorHandler(int $errno, string $errorStr, string $errorFile, int $errorLine): bool
    {
        if (!(error_reporting() & $errno))
            return false;

        $date = (new \DateTime())->format('Y-m-d H:i:s');
        $format = "[%s] [%s] %s in %s(%d)" . PHP_EOL;

        switch ($errno) {
            case E_DEPRECATED || E_USER_DEPRECATED:
                $level = 'DEPRECATED';
                break;
            case E_NOTICE || E_USER_NOTICE:
                $level = 'NOTICE';
                break;
            case E_WARNING:
                $level = 'WARNING';
                break;
            default:
                return false;
        }

        $message = sprintf($format, $date, $level, $errorStr, $errorFile, $errorLine);

        return $this->write($this->fileLog, $message);
    }

    public function push(string $message, ?string $level = null): void
    {
        $this->write($this->fileLog, $message);
    }

    protected function write(string $path, string $content): bool
    {
        try {
            $fileContent = File\read($path) . $content;
            File\write($path, $fileContent);
            return true;
        } catch (FilesystemException) {
            return false;
        }
    }

    /**
     * Restore custom error handler
     */
    public function __destruct()
    {
        $this->deactivate();
    }
}

