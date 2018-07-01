<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NumberNotInRange implements Rule
{
    // Source of model
    public $source;

    // Column that hold min value
    public $minColumn;
    public $minValue;

    // Column that hold max value
    public $maxColumn;
    public $maxValue;

    // Ignore this id for updating table
    public $ignoreId;

    /**
     * Create a new rule instance.
     *
     * @param $source
     * @param $minColumn
     * @param $maxColumn
     * @param $minValue
     * @param $maxValue
     * @param $ignoreId
     */
    public function __construct($source, $minColumn, $maxColumn, $minValue, $maxValue, $ignoreId = null)
    {
        $this->source = $source;
        $this->minColumn = $minColumn;
        $this->maxColumn = $maxColumn;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Number should not in group range.
     *
     * [1-20] = number between 1-20 is not allowed
     * [1-20] = 21 is allowed, 0 is allowed
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $exists = $this->source::where(function ($q) use ($value) {
                $q->where(function ($q) use ($value) {
                    // Find number between range
                    // For example database has number in range 1 - 20
                    // Then any number between that number is not allowed
                    $q->where($this->minColumn, '<=', $value)->where($this->maxColumn, '>=', $value);
                })->orWhere(function ($q) {
                    // Find number between range when min value outside range
                    // For example database has number in range 1 - 20
                    // Then any number like 0 - 21 is not allowed
                    $q->where($this->minColumn, '>=', $this->minValue)->where($this->maxColumn, '<=', $this->maxValue);
                });
            });

        // Check if has ignore id
        // Usually used for update form
        if ($this->ignoreId) {
            $exists->where('id', '!=', $this->ignoreId);
        }

        $exists = $exists->first();

        if ($exists) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Number in range is exists.';
    }
}
