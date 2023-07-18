<?php

namespace SFW\Cacher;

/**
 * Abstraction for all caches.
 */
abstract class Driver implements \Psr\SimpleCache\CacheInterface
{
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
     * Throws \SFW\Cacher\InvalidArgumentException
     */
    abstract public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Set multiple values by multiple keys.
     *
     * Throws \SFW\Cacher\InvalidArgumentException
     */
    abstract public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;

    /**
     * Delete multiple values by multiple keys.
     *
     * Throws \SFW\Cacher\InvalidArgumentException
     */
    abstract public function deleteMultiple(iterable $keys): bool;

    /**
     * Cheking for existing value by key.
     */
    abstract public function has(string $key): bool;

    /**
     * Normalize TTL to number.
     */
    protected function fixTtl(mixed $ttl): int
    {
        if (!isset($ttl)) {
            return 0;
        }

        if (is_numeric($ttl)) {
            return max((int) $ttl, 0);
        }

        if ($ttl instanceof \DateInterval) {
            return (new \DateTime())->setTimestamp(0)->add($ttl)->getTimestamp();
        }

        return 0;
    }
}
