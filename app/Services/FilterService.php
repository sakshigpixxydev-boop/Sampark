<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FilterService
{
    /**
     * Save filters to session
     *
     * @param string $key
     * @param array $filters
     * @return void
     */
    public function saveFilters(string $key, array $filters): void
    {
        // Remove empty values
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '' && $value !== [];
        });

        Session::put("filters.{$key}", $filters);
    }

    /**
     * Get filters from session
     *
     * @param string $key
     * @return array
     */
    public function getFilters(string $key): array
    {
        return Session::get("filters.{$key}", []);
    }

    /**
     * Clear filters from session
     *
     * @param string $key
     * @return void
     */
    public function clearFilters(string $key): void
    {
        Session::forget("filters.{$key}");
    }

    /**
     * Merge request filters with session filters
     *
     * @param Request $request
     * @param string $key
     * @return array
     */
    public function mergeFilters(Request $request, string $key): array
    {
        $sessionFilters = $this->getFilters($key);
        $requestFilters = $request->except(['page', '_token']);

        // If request has filters, use them and save to session
        if (!empty($requestFilters)) {
            $this->saveFilters($key, $requestFilters);
            return $requestFilters;
        }

        // Otherwise, use session filters
        return $sessionFilters;
    }

    /**
     * Apply filters to a query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @param array $filterMap
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilters($query, array $filters, array $filterMap)
    {
        foreach ($filters as $key => $value) {
            if (!isset($filterMap[$key]) || empty($value)) {
                continue;
            }

            $config = $filterMap[$key];
            $type = $config['type'] ?? 'exact';
            $column = $config['column'] ?? $key;

            switch ($type) {
                case 'like':
                    $query->where($column, 'LIKE', "%{$value}%");
                    break;

                case 'exact':
                    $query->where($column, $value);
                    break;

                case 'in':
                    if (is_array($value)) {
                        $query->whereIn($column, $value);
                    }
                    break;

                case 'date_range':
                    if (isset($config['from']) && isset($filters[$config['from']])) {
                        $query->where($column, '>=', $filters[$config['from']]);
                    }
                    if (isset($config['to']) && isset($filters[$config['to']])) {
                        $query->where($column, '<=', $filters[$config['to']]);
                    }
                    break;

                case 'relation':
                    $relation = $config['relation'];
                    $relationColumn = $config['relation_column'];
                    $query->whereHas($relation, function ($q) use ($relationColumn, $value) {
                        $q->where($relationColumn, $value);
                    });
                    break;

                case 'custom':
                    if (isset($config['callback']) && is_callable($config['callback'])) {
                        $config['callback']($query, $value);
                    }
                    break;
            }
        }

        return $query;
    }

    /**
     * Get active filter count
     *
     * @param array $filters
     * @return int
     */
    public function getActiveFilterCount(array $filters): int
    {
        return count(array_filter($filters, function ($value) {
            return $value !== null && $value !== '' && $value !== [];
        }));
    }
}
