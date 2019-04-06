<?php

namespace App\Http\Requests;

class ValidationRule {

  public static function deliveryFee()
  {
    return 'numeric|min:0';
  }

  public static function discountPercent()
  {
    return 'numeric|between:0,100';
  }

  public static function discountValue()
  {
    return 'numeric|min:0';
  }
  
  public static function foreignKey(String $table, String $column = 'id')
  {
    return "bail|required|integer|min:0|exists:tenant.$table,$column";
  }
  
  public static function optionalForeignKey(String $table, String $column = 'id')
  {
    return "bail|nullable|integer|min:0|exists:tenant.$table,$column";
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
}
