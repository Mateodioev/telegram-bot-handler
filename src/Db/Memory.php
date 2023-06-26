<?php

namespace Mateodioev\TgHandler\Db;

use Mateodioev\TgHandler\{Bot, RunState};

/**
 * Save data in memory
 */
class Memory implements DbInterface
{
    /**
     * @var array<string, mixed> The database
     */
    private array $db = [];

    public function __construct()
    {
        // This is why the data is missing in every request
        if (Bot::$state === RunState::webhook)
            throw new DbException('Can\'t use Memory db while bot is runing in webhook mode');
    }

    /**
     * @return bool Always return true
     */
    public function save(string $key, mixed $value): bool
    {
        $this->db[$key] = $value;
        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->db[$key] ?? $default;
    }

    public function exists(string $key): bool
    {
        return isset($this->db[$key]);
    }

    /**
     * @return bool Always return true
     */
    public function delete(string $key): bool
    {
        unset($this->db[$key]);
        return true;
    }
}