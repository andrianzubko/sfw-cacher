<?php

declare(strict_types=1);

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
     * If extension not loaded then does nothing.
     *
     * @throws Exception\Runtime
     */
    abstract public function __construct(array $options = []);

    /**
     * Fetches a value from the cache.
     */
    abstract public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     */
    abstract public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;

    /**
     * Delete an item from the cache by its unique key.
     */
    abstract public function delete(string $key): bool;

    /**
     * Not implemented!
     */
    public function clear(): bool
    {
        return false;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @throws Exception\InvalidArgument
     */
    abstract public function getMultiple(iterable $keys, mixed $default = null): array;

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @throws Exception\InvalidArgument
     */
    abstract public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @throws Exception\InvalidArgument
     */
    abstract public function deleteMultiple(iterable $keys): bool;

    /**
     * Determines whether an item is present in the cache.
     */
    abstract public function has(string $key): bool;

    /**
     * Checks keys.
     *
     * @throws Exception\InvalidArgument
     */
    protected function checkKeys(iterable $keys): array
    {
        foreach ($keys as $key) {
            if (!\is_string($key) && !\is_int($key)) {
                throw new Exception\InvalidArgument('Keys must be strings');
            }
        }

        return iterator_to_array($keys);
    }

    /**
     * Checks values.
     *
     * @throws Exception\InvalidArgument
     */
    protected function checkValues(iterable $values): array
    {
        foreach ($values as $key => $value) {
            if (!\is_string($key) && !\is_int($key)) {
                throw new Exception\InvalidArgument('Keys must be strings');
            }
        }

        return iterator_to_array($values);
    }

    /**
     * Normalizes TTL.
     */
    protected function fixTtl(mixed $ttl, ?int $zero = 0): ?int
    {
        if ($ttl !== null) {
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

        return $ttl == 0 ? $zero : $ttl;
    }
}
