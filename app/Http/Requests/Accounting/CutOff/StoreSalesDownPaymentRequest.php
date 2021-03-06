<?php

namespace App\Http\Requests\Accounting\CutOff;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesDownPaymentRequest extends FormRequest
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
            'chart_of_account_id' => 'required',
        ];
    }
}
