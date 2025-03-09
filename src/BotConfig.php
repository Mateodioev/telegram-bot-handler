<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler;

use Mateodioev\TgHandler\Db\{DbInterface, Memory};
use Mateodioev\TgHandler\Log\{Logger, PhpNativeStream};
use Psr\Log\LoggerInterface;

use function boolval;
use function is_string;
use function strtolower;

class BotConfig
{
    public const string DEFAULT_DB = Memory::class;
    public const string DEFAULT_STREAM_LOGGER = PhpNativeStream::class;

    protected ?string $botToken = null;
    protected ?DbInterface $db = null;
    protected ?LoggerInterface $logger = null;
    protected bool $async = false;

    private static array $envToken = [
        'botToken' => 'BOT_TOKEN',
        'db' => 'BOT_DB',
        'logger' => 'BOT_LOGGER',
        'async' => 'BOT_ENABLE_ASYNC'
    ];

    public static function fromEnv(): BotConfig
    {
        $obj = new static();
        $obj->setToken(self::env(self::$envToken['botToken']));

        if (($db = self::env(self::$envToken['db'])) !== null) {
            $obj->setDbStr($db);
        }

        if (($logger = self::env(self::$envToken['logger'])) !== null) {
            $obj->setLoggerStr($logger);
        }

        if (($async = self::env(self::$envToken['async'])) !== null) {
            $obj->setAsync($async);
        }

        return $obj;
    }

    /**
     * Get bot token
     */
    public function token(): string
    {
        return $this->botToken ?? '';
    }

    public function db(): DbInterface
    {
        if ($this->db instanceof DbInterface) {
            return $this->db;
        }

        $this->db = $this->createClass(self::DEFAULT_DB);
        /** @var DbInterface $this->db */
        return $this->db;
    }

    public function logger(): LoggerInterface
    {
        if ($this->logger instanceof LoggerInterface) {
            return $this->logger;
        }

        $stream = $this->createClass(self::DEFAULT_STREAM_LOGGER);
        $this->logger = new Logger($stream->activate(__DIR__));
        /** @var LoggerInterface $this->logger */
        return $this->logger;
    }

    public function async(): bool
    {
        return $this->async;
    }

    public function setToken(string $token): static
    {
        $this->botToken = $token;
        return $this;
    }

    public function setDb(DbInterface $db): static
    {
        $this->db = $db;
        return $this;
    }

    public function setDbStr(string $dbClass): static
    {
        return $this->setDb($this->createClass($dbClass));
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function setLoggerStr(string $loggerClass): static
    {
        return $this->setLogger($this->createClass($loggerClass));
    }

    public function setAsync(bool|string $value): static
    {
        $this->async = self::evalbool($value);
        return $this;
    }

    protected static function env(string $key, $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    protected static function evalbool($value): bool
    {
        if (is_string($value) && strtolower($value) === 'false') {
            return false;
        }

        return boolval($value);
    }

    private function createClass(string $class): object
    {
        return new $class();
    }
}
