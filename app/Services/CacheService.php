<?php

namespace ECommerce\App\Services;

use PDO;

class CacheService
{
    private $connection;
    private $defaultTTL = 3600; // 1 hour

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get cached value
     */
    public function get(string $key): mixed
    {
        $stmt = $this->connection->prepare(
            "SELECT cache_value FROM cache_entries 
             WHERE cache_key = ? AND (expires_at IS NULL OR expires_at > NOW())"
        );
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? unserialize($result['cache_value']) : null;
    }

    /**
     * Set cached value
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $expiresAt = $ttl > 0 ? date('Y-m-d H:i:s', time() + $ttl) : null;
        $serialized = serialize($value);

        // Try insert first
        $stmt = $this->connection->prepare(
            "INSERT INTO cache_entries (cache_key, cache_value, expires_at) 
             VALUES (?, ?, ?)"
        );

        try {
            $stmt->execute([$key, $serialized, $expiresAt]);
            return true;
        } catch (\Exception $e) {
            // Key exists, update instead
            $stmt = $this->connection->prepare(
                "UPDATE cache_entries 
                 SET cache_value = ?, expires_at = ?, updated_at = NOW()
                 WHERE cache_key = ?"
            );
            return $stmt->execute([$serialized, $expiresAt, $key]);
        }
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete cached value
     */
    public function forget(string $key): bool
    {
        $stmt = $this->connection->prepare(
            "DELETE FROM cache_entries WHERE cache_key = ?"
        );
        return $stmt->execute([$key]);
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM cache_entries");
        return $stmt->execute();
    }

    /**
     * Clear expired cache
     */
    public function flushExpired(): int
    {
        $stmt = $this->connection->prepare(
            "DELETE FROM cache_entries WHERE expires_at < NOW() AND expires_at IS NOT NULL"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Get or cache
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Increment value
     */
    public function increment(string $key, int $value = 1): int
    {
        $current = (int) ($this->get($key) ?? 0);
        $new = $current + $value;
        $this->set($key, $new);

        return $new;
    }

    /**
     * Decrement value
     */
    public function decrement(string $key, int $value = 1): int
    {
        $current = (int) ($this->get($key) ?? 0);
        $new = max(0, $current - $value);
        $this->set($key, $new);

        return $new;
    }
}
