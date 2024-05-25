<?php

declare(strict_types=1);

/**
 * (c) 2024 Multividas. All rights reserved.
 * Unauthorized use prohibited.
 * Website: https://www.multividas.com
 */

namespace Multividas\QueryFilters\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Multividas\QueryFilters\Interfaces\QueryFiltersRepositoryInterface;

class QueryFiltersRepository implements QueryFiltersRepositoryInterface
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
     * Method applyFilters
     *
     * @param Collection|EloquentCollection|JsonResource $collection
     *
     * @return array
     */
    public function applyFilters(Collection|EloquentCollection|JsonResource $collection): array
    {
        if ($collection instanceof Collection || $collection instanceof EloquentCollection) {
            $transformer = $collection->first()?->transformer ?? null;
        } elseif ($collection instanceof JsonResource) {
            $transformer = $collection->collects ?? null;
        }

        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);

        $paginatedResult = $this->paginateData($collection);

        return [
            'data' => $paginatedResult['data'],
            'meta' => $paginatedResult['meta'],
        ];
    }

    /**
     * Method filterData
     *
     * @param Collection|EloquentCollection|JsonResource $collection
     * @param ?string $transformer
     *
     * @return Collection|EloquentCollection|JsonResource
     */
    public function filterData(
        Collection|EloquentCollection|JsonResource $collection,
        ?string $transformer
    ): Collection|EloquentCollection|JsonResource {
        if (isset(request()->query()['filters'])) {
            foreach (request()->query()['filters'] as $query) {
                if (!is_null($transformer) && is_callable([$transformer, 'originalAttribute'])) {
                    $queryField = $transformer::originalAttribute($query['field']);
                } else {
                    $queryField = $query['field'];
                }

                $collection = $collection->where($queryField, $query['value']);
            }
        }

        return $collection;
    }

    /**
     * Method sortData
     *
     * @param Collection|EloquentCollection|JsonResource $collection
     * @param ?string $transformer
     *
     * @return Collection|EloquentCollection|JsonResource
     */
    public function sortData(
        Collection|EloquentCollection|JsonResource $collection,
        ?string $transformer
    ): Collection|EloquentCollection|JsonResource {
        if (request()->has('_sort')) {
            if (!is_null($transformer) && is_callable([$transformer, 'originalAttribute'])) {
                $sortField = $transformer::originalAttribute(request()->_sort);
            } else {
                $sortField = request()->_sort;
            }

            $sortOrder = request()->has('_order') && request()->_order == 'desc' ? true : false;

            $collection = $collection->sortBy($sortField, SORT_REGULAR, $sortOrder);
        }

        return $collection;
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

    /**
     * Method paginateData
     *
     * @param Collection|EloquentCollection|JsonResource $collection
     *
     * @return array
     */
    public function paginateData(Collection|EloquentCollection|JsonResource $collection): array
    {
        $rules = [
            'per_page' => ['integer', 'min:2', 'max:50'],
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $perPage = (int) request()->input('per_page', 10);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginatedCollection = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $paginatedCollection,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query()
            ]
        );

        $paginated->appends(request()->all());

        $paginationData = [
            'total' => $paginated->total(),
            'count' => $paginated->count(),
            'per_page' => $paginated->perPage(),
            'current_page' => $paginated->currentPage(),
            'total_pages' => $paginated->lastPage(),
        ];

        $links = [];

        if ($paginated->previousPageUrl() !== null) {
            $links['prev'] = $paginated->previousPageUrl();
        }

        if ($paginated->nextPageUrl() !== null) {
            $links['next'] = $paginated->nextPageUrl();
        }

        $links = array_filter($links, fn ($url) => $url !== null);

        if (!empty($links)) {
            $paginationData['links'] = $links;
        }

        $meta = $paginated->total() > $perPage ? ['pagination' => $paginationData] : [];

        return [
            'data' => $paginatedCollection,
            'meta' => $meta,
        ];
    }
}
