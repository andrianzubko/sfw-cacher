<?php

namespace SFW\Cacher;

/**
 * Abstraction for all caches.
 */
abstract class Driver implements \Psr\SimpleCache\CacheInterface
{
    /**
     * Default TTl.
     */
    protected int $ttl = 0;

    /**
     * If extension not loaded then do nothing.
     *
     * @throws Exception\Runtime
     */
    abstract public function __construct(array $options = []);

    /**
     * Get some value by key.
     */
    abstract public function get(string $key, mixed $default = null): mixed;

    /**
     * Set some value by key.
     */
    abstract public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;

    /**
     * Delete some value by key.
     */
    abstract public function delete(string $key): bool;

    /**
     * Clear cache not implemented!
     */
    abstract public function clear(): bool;

    /**
     * Get multiple values by multiple keys.
     *
     * @throws Exception\InvalidArgument
     */
    abstract public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Set multiple values by multiple keys.
     *
     * @throws Exception\InvalidArgument
     */
    abstract public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;

    /**
     * Delete multiple values by multiple keys.
     *
     * @throws Exception\InvalidArgument
     */
    abstract public function deleteMultiple(iterable $keys): bool;

    /**
     * Checking for existing value by key.
     */
    abstract public function has(string $key): bool;

    /**
     * Check keys.
     *
     * @throws Exception\InvalidArgument
     */
    protected function checkKeys(iterable $keys): array
    {
        foreach ($keys as $key) {
            if (!is_string($key)
                && !is_int($key)
            ) {
                throw new Exception\InvalidArgument('Keys must be strings');
            }
        }

        return iterator_to_array($keys);
    }

    /**
     * Check values.
     *
     * @throws Exception\InvalidArgument
     */
    protected function checkValues(iterable $values): array
    {
        foreach ($values as $key => $value) {
            if (!is_string($key)
                && !is_int($key)
            ) {
                throw new Exception\InvalidArgument('Keys must be strings');
            }
        }

        return iterator_to_array($values);
    }

    /**
     * Normalize TTL.
     */
    protected function fixTtl(mixed $ttl, ?int $zero = 0): ?int
    {
        if (isset($ttl)) {
            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime())->setTimestamp(0)->add($ttl)->getTimestamp();
            } else {
                $ttl = (int) $ttl;
            }

            if ($ttl < 0) {
                $ttl = 0;
            }
        } else {
            $ttl = $this->ttl;
        }

        return $ttl ?: $zero;
    }
}
