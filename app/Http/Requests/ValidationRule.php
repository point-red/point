<?php

namespace App\Http\Requests;

class ValidationRule
{
    public static function converter()
    {
        return 'required|numeric';
    }

    public static function deliveryFee()
    {
        return 'numeric|min:0';
    }

    public static function discountPercent()
    {
        return 'nullable|numeric|between:0,100';
    }

    public static function discountValue()
    {
        return 'numeric|min:0';
    }

    /**
     * User must provide this column, and can not be null value.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public static function foreignKey(string $table, string $column = 'id')
    {
        return "bail|required|integer|min:0|exists:tenant.$table,$column";
    }

    /**
     * User can skip this column from input or provide null value to this column.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public static function foreignKeyNullable(string $table, string $column = 'id')
    {
        return "bail|nullable|integer|min:0|exists:tenant.$table,$column";
    }

    /**
     * User can skip this column from input, but can not provide null value
     * Useful for update validation where user do not want to change not nullable column.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public static function foreignKeyOptional(string $table, string $column = 'id')
    {
        return "bail|integer|min:0|exists:tenant.$table,$column";
    }

    public static function form()
    {
        return [
            'date' => 'required|date',
            'number'=> 'nullable|string',
            'increment_group' => 'required|integer',
        ];
    }

    public static function quantity()
    {
        return 'required|numeric|min:0';
    }

    public static function price()
    {
        return 'required|numeric|min:0';
    }

    public static function needDownPayment()
    {
        return 'numeric|min:0';
    }

    public static function tax()
    {
        return 'required_if:type_of_tax,non|numeric|min:0';
    }

    public static function typeOfTax()
    {
        return 'required|in:non,include,exclude';
    }

    public static function unit()
    {
        return 'required|string';
    }
}
