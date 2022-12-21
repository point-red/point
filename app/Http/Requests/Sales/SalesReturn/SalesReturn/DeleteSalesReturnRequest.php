<?php

namespace App\Http\Requests\Sales\SalesReturn\SalesReturn;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteSalesReturnRequest extends FormRequest
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
        $deleteRule = [
            'reason' => 'required|max:255',
        ];

        return array_merge($deleteRule);
    }
}
