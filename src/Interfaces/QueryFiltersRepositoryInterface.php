<?php

/**
 * (c) 2023 Multividas inc. All rights reserved.
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
}
