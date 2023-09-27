<?php

namespace SFW\Cacher;

/**
 * Nocache.
 */
class Nocache extends Driver
{
    /**
     * Do nothing.
     */
    public function __construct(array $options = [])
    {
    }

    /**
     * Get some value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * Set some value by key.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        return false;
    }

    /**
     * Delete some value by key.
     */
    public function delete(string $key): bool
    {
        return false;
    }

    /**
     * Clear cache not implemented!
     */
    public function clear(): bool
    {
        return false;
    }

    /**
     * Get multiple values by multiple keys.
     *
     * @throws InvalidArgumentException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->checkKeys($keys);

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $default;
        }

        return $values;
    }

    /**
     * Set multiple values by multiple keys.
     *
     * @throws InvalidArgumentException
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $this->checkValues($values);

        return false;
    }

    /**
     * Delete multiple values by multiple keys.
     *
     * @throws InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $this->checkKeys($keys);

        return false;
    }

    /**
     * Checking for existing value by key.
     */
    public function has(string $key): bool
    {
        return false;
    }
}
