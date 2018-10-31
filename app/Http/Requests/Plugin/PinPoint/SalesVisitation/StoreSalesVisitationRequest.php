<?php

namespace App\Http\Requests\Plugin\PinPoint\SalesVisitation;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesVisitationRequest extends FormRequest
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
            'customer' => 'required',
            'group' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'similar_product' => 'required',
            'interest_reason' => 'required',
            'not_interest_reason' => 'required',
            'payment_method' => 'required',
        ];
    }
}
