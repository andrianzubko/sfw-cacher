<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace SFW\Cacher;

/**
 * Redis.
 */
class Redis extends Driver
{
    /**
     * Redis instance.
     */
    protected \Redis $redis;

    /**
     * If extension not loaded then do nothing.
     *
     * @throws RuntimeException
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('redis')) {
            return;
        }

        try {
            $this->ttl = $options['ttl'] ?? $this->ttl;

            $this->redis = new \Redis();

            $options['connect'] ??= ['127.0.0.1', 6379, 2.5];

            $this->redis->connect(...$options['connect']);

            $options['options'] ??= [];

            $options['options'][\Redis::OPT_PREFIX] = $options['ns'] ?? md5(__FILE__);

            $options['options'][\Redis::OPT_SERIALIZER] ??= \Redis::SERIALIZER_PHP;

            foreach ($options['options'] as $key => $value) {
                $this->redis->setOption($key, $value);
            }
        } catch (\RedisException $error) {
            throw new RuntimeException($error->getMessage());
        }
    }

    /**
     * Get some value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->redis)) {
            return $default;
        }

        try {
            [$value, $exists] = $this->redis->multi()->get($key)->exists($key)->exec();
        } catch (\RedisException) {
            return $default;
        }

        return $exists ? $value : $default;
    }

    /**
     * Set some value by key.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if (!isset($this->redis)) {
            return false;
        }

        try {
            return $this->redis->set($key, $value, $this->fixTtl($ttl, null));
        } catch (\RedisException) {
            return false;
        }
    }

    /**
     * Delete some value by key.
     */
    public function delete(string $key): bool
    {
        if (!isset($this->redis)) {
            return false;
        }

        try {
            return (bool) $this->redis->del($key);
        } catch (\RedisException) {
            return false;
        }
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
        $keys = $this->checkKeys($keys);

        $fetched = [];

        if (isset($this->redis)) {
            try {
                $this->redis->multi()->mGet($keys);

                foreach ($keys as $key) {
                    $this->redis->exists($key);
                }

                $result = $this->redis->exec();

                foreach ($result[0] as $i => $value) {
                    if ($result[$i + 1]) {
                        $fetched[$keys[$i]] = $value;
                    }
                }
            } catch (\RedisException) {}
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
     * @throws InvalidArgumentException
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $values = $this->checkValues($values);

        if (!isset($this->redis)) {
            return false;
        }

        $ttl = $this->fixTtl($ttl, null);

        try {
            $success = true;

            foreach ($values as $key => $value) {
                $success = $this->redis->set($key, $value, $ttl)
                    ? $success : false;
            }

            return $success;
        } catch (\RedisException) {
            return false;
        }
    }

    /**
     * Delete multiple values by multiple keys.
     *
     * @throws InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = $this->checkKeys($keys);

        if (!isset($this->redis)) {
            return false;
        }

        try {
            $this->redis->del($keys);
        } catch (\RedisException) {
            return false;
        }

        return true;
    }

    /**
     * Checking for existing value by key.
     */
    public function has(string $key): bool
    {
        if (!isset($this->redis)) {
            return false;
        }

        try {
            return (bool) $this->redis->exists($key);
        } catch (\RedisException) {
            return false;
        }
    }
}
