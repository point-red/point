<?php

namespace App\Http\Requests\Psychotest\PositionCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePositionCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "category_max" => ["required", "numeric"],
            "category_min" => ["required", "numeric"],
            "position_id" => ["required", "numeric"],
            "category_id" => ["required", "numeric"]
        ];
    }
}
