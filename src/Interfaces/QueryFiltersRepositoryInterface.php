<?php

declare(strict_types=1);

/**
 * (c) 2024 Multividas. All rights reserved.
 * Unauthorized use prohibited.
 * Website: https://www.multividas.com
 */

namespace Multividas\QueryFilters\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface QueryFiltersRepositoryInterface
{
    public function applyFilters(Collection|EloquentCollection|JsonResource $collection): array|JsonResponse;

    public function filterData(
        Collection|EloquentCollection|JsonResource $collection,
        ?string $transformer
    ): Collection|EloquentCollection|JsonResource;

    public function sortData(
        Collection|EloquentCollection|JsonResource $collection,
        ?string $transformer
    ): Collection|EloquentCollection|JsonResource;

    public function cacheData(
        Collection|EloquentCollection|JsonResource $collection
    ): Collection|EloquentCollection|JsonResource;

    public function paginateData(Collection|EloquentCollection|JsonResource $collection): array;
}
