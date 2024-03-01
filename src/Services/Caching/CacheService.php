<?php

/**
 * (c) 2023 Multividas inc. All rights reserved.
 * Unauthorized use prohibited.
 * Website: https://www.multividas.com
 */

namespace Multividas\QueryFilters\Services\Caching;

use Illuminate\Cache\Repository;

class CacheService
{
    public function __construct(
        private Repository $cacheRepository
    ) {
    }

    public function remember(string $key, \Closure|\DateTimeInterface|\DateInterval|int|null $ttl, \Closure $callback)
    {
        return $this->cacheRepository->remember($key, $ttl, $callback);
    }
    
    /**
     * Method forget
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget(string $key): bool
    {
        return $this->cacheRepository->forget($key);
    }
}
