<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace SFW\Cacher;

/**
 * Memcached.
 */
class Memcached extends Driver
{
    /**
     * Memcached instance.
     */
    protected \Memcached $memcached;

    /**
     * If extension not loaded then does nothing.
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('memcached')) {
            return;
        }

        $this->ttl = $options['ttl'] ?? $this->ttl;

        $this->memcached = new \Memcached();

        $options['servers'] ??= [['127.0.0.1', 11211]];

        $this->memcached->addServers($options['servers']);

        $options['options'] ??= [];

        $options['options'][\Memcached::OPT_PREFIX_KEY] = $options['ns'] ?? md5(__FILE__);

        $this->memcached->setOptions($options['options']);
    }

    /**
     * Fetches a value from the cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->memcached)) {
            return $default;
        }

        $values = $this->memcached->getMulti([$key]);

        return $values ? $values[$key] : $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if (!isset($this->memcached)) {
            return false;
        }

        return $this->memcached->set($key, $value, $this->fixTtl($ttl));
    }

    /**
     * Delete an item from the cache by its unique key.
     */
    public function delete(string $key): bool
    {
        if (!isset($this->memcached)) {
            return false;
        }

        return $this->memcached->delete($key);
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @throws Exception\InvalidArgument
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = $this->checkKeys($keys);

        if (isset($this->memcached)) {
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
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @throws Exception\InvalidArgument
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $values = $this->checkValues($values);

        if (!isset($this->memcached)) {
            return false;
        }

        return $this->memcached->setMulti($values, $this->fixTtl($ttl));
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @throws Exception\InvalidArgument
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $this->checkKeys($keys);

        if (!isset($this->memcached)) {
            return false;
        }

        $this->memcached->deleteMulti($keys);

        return true;
    }

    /**
     * Determines whether an item is present in the cache.
     */
    public function has(string $key): bool
    {
        if (!isset($this->memcached)) {
            return false;
        }

        return (bool) $this->memcached->getMulti([$key]);
    }
}
