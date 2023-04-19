<?php

namespace Mateodioev\TgHandler\Log;

// use Amp\File\FilesystemException;
use Mateodioev\Utils\Exceptions\FileException;
use Mateodioev\Utils\Files;

/**
 * Log php errors into a file setting an error_handler
 */
class PhpNativeStream implements Stream
{
    public string $fileLog;

    public function activate(string $dir, ?string $file = null): static
    {
        if (is_dir($dir) && $file !== null) {
            throw new FileException('Invalid dir');
        }

        if ($file !== null) {
            if (!Files::isFile($file)) {
                throw new FileException('Invalid file');
            } else {
                $this->setFile($file);
            }
        } else {
            $this->setFile($dir . '/' . \date('Y-m-d') . '-php_error.log');
        }

        \error_reporting(E_ALL);
        \ini_set('display_errors', false);
        \ini_set('log_errors', true);
        \ini_set('error_log', $this->fileLog);

        set_error_handler($this->errorHandler(...));

        return $this;
    }

    public function setFile(string $path): static
    {
        $this->fileLog = $path;
        return $this;
    }

    public function deactivate()
    {
        restore_error_handler();
    }

    public function errorHandler(int $errno, string $errorStr, string $errorFile, int $errorLine): bool
    {
        if (!(error_reporting() & $errno)) return false;

        $date = (new \DateTime())->format('Y-m-d H:i:s');
        $format = "[%s] [%s] %s in %s(%d)" . PHP_EOL;
        $level = 'ALL';

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

    public function push(string $message): void
    {
        $this->write($this->fileLog, $message);
    }

    protected function write(string $path, string $content)
    {
        return (bool) file_put_contents($path, $content, FILE_APPEND);

        /* try {
            \Amp\File\write($path, $content);
            return true;
        } catch (FilesystemException $e) {
         return false;
        } */
    }
}