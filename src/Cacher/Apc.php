<?php /** @noinspection PhpComposerExtensionStubsInspection */

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
     * If extension not loaded then do nothing.
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

        return apcu_store($this->ns . $key, $value, $this->fixTtl($ttl));
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
     * @throws Exception\InvalidArgument
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = $this->checkKeys($keys);

        if (isset($this->ns)) {
            $fetched = apcu_fetch(array_map(fn($k) => $this->ns . $k, $keys));
        } else {
            $fetched = [];
        }

        $values = [];

        foreach ($keys as $key) {
            $values[$key] = isset($this->ns, $fetched[$this->ns . $key])
                ? $fetched[$this->ns . $key]
                    : $default;
        }

        return $values;
    }

    /**
     * Set multiple values by multiple keys.
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
     * Delete multiple values by multiple keys.
     *
     * @throws Exception\InvalidArgument
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $this->checkKeys($keys);

        if (!isset($this->ns)) {
            return false;
        }

        return apcu_delete(
            new \APCUIterator(array_map(fn($k) => $this->ns . $k, $keys))
        );
    }

    /**
     * Checking for existing value by key.
     */
    public function has(string $key): bool
    {
        if (!isset($this->ns)) {
            return false;
        }

        return apcu_exists($this->ns . $key);
    }
}
