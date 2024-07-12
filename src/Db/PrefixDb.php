<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Db;

class PrefixDb implements DbInterface
{
    public function __construct(
        readonly private DbInterface $db,
        readonly private string $prefix
    ) {
    }

    public function save(string $key, mixed $value): bool
    {
        return $this->db->save($this->prefix($key), $value);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->db->get($this->prefix($key), $default);
    }

    public function exists(string $key): bool
    {
        return $this->db->exists($this->prefix($key));
    }

    public function delete(string $key): bool
    {
        return $this->db->delete($this->prefix($key));
    }

    /**
     * Add prefix to key
     */
    private function prefix(string $key): string
    {
        return "{$this->prefix}$key";
    }
}
