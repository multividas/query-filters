<?php

/**
 * (c) 2024 Multividas. All rights reserved.
 * Unauthorized use prohibited.
 * Website: https://www.multividas.com
 */

namespace Multividas\QueryFilters\Facades;

use Illuminate\Support\Facades\Facade;
use Multividas\QueryFilters\Interfaces\QueryFiltersRepositoryInterface;

class QueryFilters extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QueryFiltersRepositoryInterface::class;
    }
}
