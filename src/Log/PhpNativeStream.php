<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use Amp\File;
use Amp\File\FilesystemException;
use Mateodioev\Utils\Exceptions\FileException;
use Mateodioev\Utils\Files;
use SimpleLogger\Formatters\{DefaultFormatter, Formatter};
use SimpleLogger\streams\LogResult;

use function date;
use function error_reporting;
use function ini_set;
use function is_dir;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

/**
 * Log php errors into a file setting an error_handler
 */
class PhpNativeStream implements Stream
{
    public string $fileLog;
    private Formatter $formatter;

    public function __construct(?Formatter $formatter = null)
    {
        $this->formatter = new DefaultFormatter();
    }

    /**
     * @throws FileException
     */
    public function activate(string $dir, ?string $file = null): PhpNativeStream
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
            $this->setFile($dir . $this->getFileName());
        }

        error_reporting(E_ALL);
        ini_set('display_errors', false);
        ini_set('log_errors', true);
        ini_set('error_log', $this->fileLog);

        set_error_handler($this->errorHandler(...));

        return $this;
    }

    private function getFileName(): string
    {
        return '/' . date('Y-m-d') . '-php_error.log';
    }

    public function setFile(string $path): PhpNativeStream
    {
        $this->fileLog = $path;

        return $this;
    }

    public function deactivate(): void
    {
        restore_error_handler();
    }

    public function errorHandler(int $errno, string $errorStr, string $errorFile, int $errorLine): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

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

        return $this->write($this->fileLog, new LogResult(
            level: $level,
            message: sprintf('%s in %s:%s', $errorStr, $errorFile, $errorLine),
        ));
    }

    public function push(LogResult $message, ?string $level = null): void
    {
        $this->write($this->fileLog, $message);
    }

    protected function write(string $path, LogResult $content): bool
    {
        try {
            File\openFile($path, 'a')->write($this->formatter->format($content)); // Create file if not exists
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
