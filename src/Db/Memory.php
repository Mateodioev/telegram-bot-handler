<?php

namespace Mateodioev\TgHandler\Db;

class Memory implements DbInterface
{
    /**
     * @var array<string, mixed> The database
     */
    private array $db = [];

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