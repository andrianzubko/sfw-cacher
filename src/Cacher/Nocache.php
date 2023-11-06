<?php
declare(strict_types=1);

namespace SFW\Cacher;

/**
 * Nocache.
 */
class Nocache extends Driver
{
    /**
     * Does nothing.
     */
    public function __construct(array $options = [])
    {
    }

    /**
     * Fetches a value from the cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        return false;
    }

    /**
     * Delete an item from the cache by its unique key.
     */
    public function delete(string $key): bool
    {
        return false;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @throws Exception\InvalidArgument
     */
    public function getMultiple(iterable $keys, mixed $default = null): array
    {
        $this->checkKeys($keys);

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $default;
        }

        return $values;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @throws Exception\InvalidArgument
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $this->checkValues($values);

        return false;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @throws Exception\InvalidArgument
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $this->checkKeys($keys);

        return false;
    }

    /**
     * Determines whether an item is present in the cache.
     */
    public function has(string $key): bool
    {
        return false;
    }
}
