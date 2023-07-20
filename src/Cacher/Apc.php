<?php

namespace SFW\Cacher;

/**
 * APC.
 */
class Apc extends Driver
{
    /**
     * If extension not loaded then do nothing.
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('apcu')) {
            return;
        }

        $this->options = $options;

        $this->options['ns'] ??= md5(__FILE__);
    }

    /**
     * Get some value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->options)) {
            return $default;
        }

        $value = apcu_fetch($this->options['ns'] . $key, $success);

        return $success ? $value : $default;
    }

    /**
     * Set some value by key.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if (!isset($this->options)) {
            return false;
        }

        return apcu_store($this->options['ns'] . $key, $value, $this->fixTtl($ttl ?? $this->options['ttl']));
    }

    /**
     * Delete some value by key.
     */
    public function delete(string $key): bool
    {
        if (!isset($this->options)) {
            return false;
        }

        return apcu_delete($this->options['ns'] . $key);
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

        if (isset($this->options)) {
            $fetched = apcu_fetch(
                array_map(fn($k) => $this->options['ns'] . $k, $keys)
            );
        } else {
            $fetched = [];
        }

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $fetched[$this->options['ns'] . $key] ?? $default;
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

        if (!isset($this->options)) {
            return false;
        }

        return !apcu_store(
            array_combine(
                array_map(fn($k) => $this->options['ns'] . $k, array_keys($values)), $values
            ), null, $this->fixTtl($ttl ?? $this->options['ttl'])
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

        if (!isset($this->options)) {
            return false;
        }

        return apcu_delete(
            new \APCUIterator(
                array_map(fn($k) => $this->options['ns'] . $k, $keys)
            )
        );
    }

    /**
     * Checking for existing value by key.
     */
    public function has(string $key): bool
    {
        if (!isset($this->options)) {
            return false;
        }

        return apcu_exists($this->options['ns'] . $key);
    }
}
