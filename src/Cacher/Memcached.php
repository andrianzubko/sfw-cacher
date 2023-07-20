<?php

namespace SFW\Cacher;

/**
 * Memcached.
 */
class Memcached extends Driver
{
    /**
     * Memcached object.
     */
    protected \Memcached $memcached;

    /**
     * If extension not loaded then do nothing.
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('memcached')) {
            return;
        }

        $this->options = $options;

        $this->memcached = new \Memcached();

        $this->memcached->setOptions(
            array_merge($this->options['options'] ?? [],
                [
                    \Memcached::OPT_PREFIX_KEY => $this->options['ns'] ?? md5(__FILE__),
                ]
            )
        );

        $this->memcached->addServers($this->options['servers'] ?? [['127.0.0.1', 11211]]);
    }

    /**
     * Get some value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->options)) {
            return $default;
        }

        $values = $this->memcached->getMulti([$key]);

        return $values ? $values[$key] : $default;
    }

    /**
     * Set some value by key.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if (!isset($this->options)) {
            return false;
        }

        return $this->memcached->set($key, $value, $this->fixTtl($ttl ?? $this->options['ttl']));
    }

    /**
     * Delete some value by key.
     */
    public function delete(string $key): bool
    {
        if (!isset($this->options)) {
            return false;
        }

        return $this->memcached->delete($key);
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
            $fetched = $this->memcached->getMulti($keys) ?: [];
        } else {
            $fetched = [];
        }

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $fetched[$key] ?? $default;
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

        return $this->memcached->setMulti($values, $this->fixTtl($ttl ?? $this->options['ttl']));
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

        $this->memcached->deleteMulti($keys);

        return true;
    }

    /**
     * Checking for existing value by key.
     */
    public function has(string $key): bool
    {
        if (!isset($this->options)) {
            return false;
        }

        return !!$this->memcached->getMulti([$key]);
    }
}
