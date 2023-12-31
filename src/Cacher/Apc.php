<?php /** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace SFW\Cacher;

/**
 * APC.
 */
class Apc extends Driver
{
    /**
     * Namespace.
     */
    protected string $ns;

    /**
     * If extension not loaded then does nothing.
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('apcu')) {
            return;
        }

        $this->ttl = $options['ttl'] ?? $this->ttl;

        $this->ns = $options['ns'] ?? md5(__FILE__);
    }

    /**
     * Fetches a value from the cache.
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
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if (!isset($this->ns)) {
            return false;
        }

        return apcu_store($this->ns . $key, $value, $this->fixTtl($ttl));
    }

    /**
     * Delete an item from the cache by its unique key.
     */
    public function delete(string $key): bool
    {
        if (!isset($this->ns)) {
            return false;
        }

        return apcu_delete($this->ns . $key);
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @throws Exception\InvalidArgument
     */
    public function getMultiple(iterable $keys, mixed $default = null): array
    {
        $keys = $this->checkKeys($keys);

        if (isset($this->ns)) {
            $fetched = apcu_fetch(array_map(fn($k) => $this->ns . $k, $keys));
        } else {
            $fetched = [];
        }

        $values = [];

        foreach ($keys as $key) {
            if (isset($this->ns, $fetched[$this->ns . $key])) {
                $values[$key] = $fetched[$this->ns . $key];
            } else {
                $values[$key] = $default;
            }
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

        if (!isset($this->ns)) {
            return false;
        }

        return !apcu_store(
            array_combine(
                array_map(fn($k) => $this->ns . $k, array_keys($values)), $values
            ), null, $this->fixTtl($ttl)
        );
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @throws Exception\InvalidArgument
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $this->checkKeys($keys);

        if (!isset($this->ns)) {
            return false;
        }

        return apcu_delete(new \APCUIterator(array_map(fn($k) => $this->ns . $k, $keys)));
    }

    /**
     * Determines whether an item is present in the cache.
     */
    public function has(string $key): bool
    {
        if (!isset($this->ns)) {
            return false;
        }

        return apcu_exists($this->ns . $key);
    }
}
