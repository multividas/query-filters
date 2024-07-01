<?php

declare(strict_types=1);

/**
 * (c) 2024 Multividas. All rights reserved.
 * Unauthorized use prohibited.
 * Website: https://www.multividas.com
 */

namespace Multividas\QueryFilters\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CachingService
{
    private string $url;
    private string $fullUrl;
    private string $queryString;
    private string|array|null $queryParams;

    /**
     * Method __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->url = request()->url();

        $this->queryParams = request()->query();
        ksort($this->queryParams);

        $this->queryString = http_build_query($this->queryParams);

        $this->fullUrl = "{$this->url}?{$this->queryString}";
    }

    /**
     * Method cacheData
     *
     * @param Collection|EloquentCollection|JsonResource $collection
     *
     * @return Collection|EloquentCollection|JsonResource
     */
    public function cacheData(
        Collection|EloquentCollection|JsonResource $collection
    ): Collection|EloquentCollection|JsonResource {
        return Cache::remember($this->fullUrl, now()->addSeconds(60), function () use ($collection) {
            return $collection;
        });
    }
}
