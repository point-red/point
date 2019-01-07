<?php

namespace App\Traits;

trait EloquentFilters
{
    /**
     * @param $query
     * @param $request
     */
    public function scopeEloquentFilter($query, $request)
    {
        $query->fields($request->get('fields'))
            ->sortBy($request->get('sort_by'))
            ->includes($request->get('includes'))
            ->filterEqual($request->get('filter_equal'))
            ->filterNotEqual($request->get('filter_not_equal'))
            ->filterLike($request->get('filter_like'))
            ->filterMin($request->get('filter_min'))
            ->filterMax($request->get('filter_max'))
            ->filterNull($request->get('filter_null'))
            ->filterNotNull($request->get('filter_not_null'))
            ->orFilterEqual($request->get('or_filter_equal'))
            ->orFilterNotEqual($request->get('or_filter_not_equal'))
            ->orFilterLike($request->get('or_filter_like'))
            ->orFilterMin($request->get('or_filter_min'))
            ->orFilterMax($request->get('or_filter_max'))
            ->orFilterNull($request->get('or_filter_null'))
            ->orFilterNotNull($request->get('or_filter_not_null'));
    }

    /**
     * @param $query
     * @param $values
     *
     * Examples :
     * ?sort_by=name sort name ascending
     * ?sort_by=-name sort name descending
     */
    public function scopeSortBy($query, $values)
    {
        if ($values) {
            $fields = explode(',', $values);
            foreach ($fields as $value) {
                $sort = substr($value, 0, 1) == '-' ? 'desc' : 'asc';
                if ($sort == 'desc') {
                    $field = substr($value, 1, strlen($value));
                } else {
                    $field = substr($value, 0, strlen($value));
                }
                $query->orderBy($field, $sort);
            }
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * To only get specific column, separated by comma
     * Examples :
     * ?fields=id,name
     */
    public function scopeFields($query, $values)
    {
        if ($values) {
            foreach (explode(',', $values) as $value) {
                $query->addSelect($value);
            }
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Eager Loading Specific Columns:
     * You may not always need every column from the relationships you are retrieving.
     * For this reason, Eloquent allows you to specify which columns
     * of the relationship you would like to retrieve:
     * https://laravel.com/docs/5.7/eloquent-relationships#eager-loading
     * Examples :
     * ?includes=supplier,purchaseOrderItems.allocation
     */
    public function scopeIncludes($query, $values)
    {
        if ($values) {
            foreach (explode(';', $values) as $value) {
                $query->with($value);
            }
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to show only closed form
     * ?filter_equal[form.status]=1
     */
    public function scopeFilterEqual($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->where($key, $value);
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to show only active form
     * ?filter_not_equal[form.canceled]=1
     */
    public function scopeFilterNotEqual($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->where($key, '!=', $value);
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to search for name
     * ?filter_like[name]=doe
     */
    public function scopeFilterLike($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            // search each word that separate by space
            $words = explode(' ', $value);
            foreach ($words as $word) {
                $query->where($key, 'like', '%'.$word.'%');
            }
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to get item that has stock at least 1
     * ?filter_min[stock]=1
     */
    public function scopeFilterMin($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->where($key, '>=', $value);
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to get item that has stock below 30
     * ?filter_max[stock]=30
     */
    public function scopeFilterMax($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->where($key, '<=', $value);
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to get pending form
     * ?filter_null=form.status
     */
    public function scopeFilterNull($query, $values)
    {
        if (! is_null($values)) {
            $columns = explode(',', $values);

            foreach ($columns as $key => $column) {
                $query->whereNull($column);
            }
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to get form that is not pending
     * ?filter_not_null=form.status
     */
    public function scopeFilterNotNull($query, $values)
    {
        if (! is_null($values)) {
            $columns = explode(',', $values);

            foreach ($columns as $key => $column) {
                $query->whereNotNull($column);
            }
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to get item with color black or white
     * ?filter_equal[item.color]=black&or_filter_equal[item.color]=white
     */
    public function scopeOrFilterEqual($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->orWhere($key, $value);
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to get item with color black or white
     * ?filter_equal[item.color]=black&or_filter_not_equal[item.color]=white
     */
    public function scopeOrFilterNotEqual($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->orWhere($key, '!=', $value);
        }
    }

    /**
     * @param $query
     * @param $values
     *
     * Example to get search by supplier name or description
     * ?filter_like[supplier.name]=gold&or_filter_like[form.notes]=gold
     */
    public function scopeOrFilterLike($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            // search each word that separate by space
            $words = explode(' ', $value);
            foreach ($words as $word) {
                $query->orWhere($key, 'like', '%'.$word.'%');
            }
        }
    }

    /**
     * @param $query
     * @param $values
     */
    public function scopeOrFilterMin($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->orWhere($key, '<=', $value);
        }
    }

    /**
     * @param $query
     * @param $values
     */
    public function scopeOrFilterMax($query, $values)
    {
        $values = $this->convertJavascriptObjectToArray($values);

        foreach ($values as $key => $value) {
            $query->orWhere($key, '>=', $value);
        }
    }

    /**
     * @param $query
     * @param $values
     */
    public function scopeOrFilterNull($query, $values)
    {
        if (! is_null($values)) {
            $columns = explode(',', $values);

            foreach ($columns as $key => $column) {
                $query->orWhereNull($column);
            }
        }
    }

    /**
     * @param $query
     * @param $values
     */
    public function scopeOrFilterNotNull($query, $values)
    {
        if (! is_null($values)) {
            $columns = explode(',', $values);

            foreach ($columns as $key => $column) {
                $query->orWhereNotNull($column);
            }
        }
    }

    /**
     * If values is javascript object then convert it to array.
     *
     * @param $values
     *
     * @return array
     */
    private function convertJavascriptObjectToArray($values)
    {
        if (is_null($values)) {
            return [];
        }

        if (! is_array($values)) {
            return json_decode($values, true);
        }

        return $values;
    }
}
