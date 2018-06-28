<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NumberNotInRange implements Rule
{
    // Source of model
    public $source;

    // Column that hold min value
    public $minColumn;

    // Column that hold max value
    public $maxColumn;

    // Ignore this id for updating table
    public $ignoreId;

    /**
     * Create a new rule instance.
     *
     * @param $source
     * @param $minColumn
     * @param $maxColumn
     * @param $ignoreId
     */
    public function __construct($source, $minColumn, $maxColumn, $ignoreId = null)
    {
        $this->source = $source;
        $this->minColumn = $minColumn;
        $this->maxColumn = $maxColumn;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Number should not in group range.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $exists = $this->source::where($this->minColumn, '<=', $value)->where($this->maxColumn, '>=', $value);

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
