<?php

namespace App\Http\Requests;

class ValidationRule {

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
   * User must provide this column, and can not be null value
   */
  public static function foreignKey(String $table, String $column = 'id')
  {
    return "bail|required|integer|min:0|exists:tenant.$table,$column";
  }
  
  /**
   * User can skip this column from input or provide null value to this column
   */
  public static function foreignKeyNullable(String $table, String $column = 'id')
  {
    return "bail|nullable|integer|min:0|exists:tenant.$table,$column";
  }

  /**
   * User can skip this column from input, but can not provide null value
   * Useful for update validation where user do not want to change not nullable column
   */
  public static function foreignKeyOptional(String $table, String $column = 'id')
  {
    return "bail|integer|min:0|exists:tenant.$table,$column";
  }

  public static function form()
  {
    return [
      'date' => 'required|date',
      'number'=> 'nullable|string',
      'increment_group' => 'required|integer',
      'approver_id' => self::foreignKeyNullable('users'),
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
