<?php

namespace Mateodioev\TgHandler\Db;

interface DbInterface
{
    /**
     * Save a value in the database
     * @return bool Return true on success
     */
    public function save(string $key, mixed $value): bool;

    /**
     * Get value from db
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if the `$key` exists in the database
     * @return bool Return true if `$key` exists in the database
     */
    public function exists(string $key): bool;

    /**
     * Delete a value from the database
     * @return bool Return true on success
     */
    public function delete(string $key): bool;
}
