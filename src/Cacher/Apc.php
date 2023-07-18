<?php

namespace SFW\Cacher;

/**
 * APC.
 */
class Apc extends Cache
{
    /**
     * Namespace.
     */
    protected ?string $ns = null;

    /**
     * If extension not loaded then do nothing.
     */
    public function __construct(protected null|int|\DateInterval $ttl = 0, ?string $ns = null)
    {
        if (!extension_loaded('apcu')) {
            return;
        }

        $this->ns = $ns ?? md5(__FILE__);
    }

    /**
     * Get some value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->ns)) {
            return $default;
        }

        $value = apcu_fetch($this->ns . $key, $success);

        return $success ? $value : $default;
    }

    /**
     * Set some value by key.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if (!isset($this->ns)) {
            return false;
        }

        return apcu_store($this->ns . $key, $value, $this->fixTtl($ttl ?? $this->ttl));
    }

    /**
     * Delete some value by key.
     */
    public function delete(string $key): bool
    {
        if (!isset($this->ns)) {
            return false;
        }

        return apcu_delete($this->ns . $key);
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
     * Throws \SFW\Cacher\InvalidArgumentException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = iterator_to_array($keys);

        foreach ($keys as $key) {
            if (!is_string($key) && !is_int($key)) {
                throw new InvalidArgumentException('Each key must be a string');
            }
        }

        if (isset($this->ns)) {
            $fetched = apcu_fetch(
                array_map(fn($k) => $this->ns . $k, $keys)
            );
        } else {
            $fetched = [];
        }

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $fetched[$this->ns . $key] ?? $default;
        }

        return $values;
    }

    /**
     * Set multiple values by multiple keys.
     *
     * Throws \SFW\Cacher\InvalidArgumentException
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $values = iterator_to_array($values);

        foreach (array_keys($values) as $key) {
            if (!is_string($key) && !is_int($key)) {
                throw new InvalidArgumentException('Each key must be a string');
            }
        }

        if (!isset($this->ns)) {
            return false;
        }

        return !apcu_store(
            array_combine(
                array_map(fn($k) => $this->ns . $k, array_keys($values)), $values
            ), null, $this->fixTtl($ttl ?? $this->ttl)
        );
    }

    /**
     * Delete multiple values by multiple keys.
     *
     * Throws \SFW\Cacher\InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = iterator_to_array($keys);

        foreach ($keys as $key) {
            if (!is_string($key) && !is_int($key)) {
                throw new InvalidArgumentException('Each key must be a string');
            }
        }

        if (!isset($this->ns)) {
            return false;
        }

        return apcu_delete(
            new \APCUIterator(
                array_map(fn($k) => $this->ns . $k, $keys)
            )
        );
    }

    /**
     * Cheking for existing value by key.
     */
    public function has(string $key): bool
    {
        if (!isset($this->ns)) {
            return false;
        }

        return apcu_exists($this->ns . $key);
    }
}
