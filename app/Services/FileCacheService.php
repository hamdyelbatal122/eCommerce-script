<?php

namespace ECommerce\App\Services;

class FileCacheService
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?: dirname(__DIR__, 2) . '/storage/cache';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }
    }

    public function remember(string $key, int $ttlSeconds, callable $callback)
    {
        $path = $this->path($key);
        if (is_file($path)) {
            $payload = json_decode((string) file_get_contents($path), true);
            if (is_array($payload) && ($payload['expires_at'] ?? 0) > time()) {
                return $payload['value'] ?? null;
            }
        }

        $value = $callback();
        $payload = [
            'expires_at' => time() + $ttlSeconds,
            'value' => $value,
        ];
        file_put_contents($path, json_encode($payload));
        return $value;
    }

    private function path(string $key): string
    {
        return $this->cacheDir . '/' . sha1($key) . '.json';
    }
}
