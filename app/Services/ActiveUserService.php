<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

class ActiveUserService
{
    public const INDEX_KEY = 'active_users_index';
    public const KEY_PREFIX = 'active_user:';
    public const TTL_SECONDS = 300; // 5 minutes

    /**
     * Record or refresh activity for a session.
     */
    public function touch(string $sessionId): void
    {
        $cache = $this->store();
        $index = $this->getActiveIndex($cache);

        $index[] = $sessionId;
        $index = array_values(array_unique($index));
        $index = $this->filterActive($index, $cache);

        $cache->put(self::INDEX_KEY, $index, self::TTL_SECONDS * 2);
        $cache->put($this->key($sessionId), now()->timestamp, self::TTL_SECONDS);
    }

    /**
     * Count currently active sessions (last 5 minutes).
     */
    public function count(): int
    {
        $cache = $this->store();
        $index = $this->filterActive($this->getActiveIndex($cache), $cache);
        $cache->put(self::INDEX_KEY, $index, self::TTL_SECONDS * 2);

        return count($index);
    }

    protected function key(string $sessionId): string
    {
        return self::KEY_PREFIX . $sessionId;
    }

    protected function getActiveIndex(CacheRepository $cache): array
    {
        $index = $cache->get(self::INDEX_KEY, []);
        return is_array($index) ? $index : [];
    }

    protected function filterActive(array $index, CacheRepository $cache): array
    {
        $threshold = now()->subSeconds(self::TTL_SECONDS)->timestamp;
        $filtered = [];

        foreach ($index as $sessionId) {
            $lastSeen = $cache->get($this->key($sessionId));
            if ($lastSeen && $lastSeen >= $threshold) {
                $filtered[] = $sessionId;
            }
        }

        return $filtered;
    }

    protected function store(): CacheRepository
    {
        $preferred = config('cache.default', 'file');
        $fallback = config('cache.stores.file') ? 'file' : 'array';

        try {
            return Cache::store($preferred);
        } catch (\Throwable $e) {
            return Cache::store($fallback);
        }
    }
}
