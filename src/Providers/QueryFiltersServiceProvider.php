<?php

declare(strict_types=1);

/**
 * (c) 2024 Multividas. All rights reserved.
 * Unauthorized use prohibited.
 * Website: https://www.multividas.com
 */

namespace Multividas\QueryFilters\Providers;

use Illuminate\Support\ServiceProvider;
use Multividas\QueryFilters\Repositories\QueryFiltersRepository;
use Multividas\QueryFilters\Interfaces\QueryFiltersRepositoryInterface;

class QueryFiltersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QueryFiltersRepositoryInterface::class, function () {
            return new QueryFiltersRepository();
        });
    }
}
