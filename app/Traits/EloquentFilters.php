<?php

namespace App\Traits;

trait EloquentFilters
{
    public function scopeEloquentFilter($query, $request)
    {
        $query->fields($request->get('fields'))
            ->sortBy($request->get('sort_by'))
            ->filters($request->get('filters'))
            ->includes($request->get('includes'));
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

    public function scopeFields($query, $values)
    {
        if ($values) {
            foreach (explode(',', $values) as $value) {
                $query->addSelect($value);
            }
        }
    }

    public function scopeFilters($query, $values)
    {
        if ($values) {
            // If values is javascript object then convert it to array
            if (! is_array($values)) {
                $values = json_decode($values, true);
            }

            foreach ($values as $key => $value) {
                // search each word that separate by space
                foreach (explode(' ', $value) as $word) {
                    $query->where($key, 'like', '%'.$word.'%');
                }
            }
        }
    }

    public function scopeIncludes($query, $values)
    {
        if ($values) {
            // Support multiple call relation
            foreach (explode(';', $values) as $value) {
                // Eager Loading Specific Columns:
                // You may not always need every column from the relationships you are retrieving.
                // For this reason, Eloquent allows you to specify which columns
                // of the relationship you would like to retrieve:
                // https://laravel.com/docs/5.7/eloquent-relationships#eager-loading
                $relation = explode(':', $value)[0];
                if ($this->hasRelation($relation)) {
                    $query->with($value);
                }
            }
        }
    }

    private function hasRelation($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return true;
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            //Uses PHP built in function to determine whether the returned object is a laravel relation
            return is_a($this->$key(), "Illuminate\Database\Eloquent\Relations\Relation");
        }

        return false;
    }
}
