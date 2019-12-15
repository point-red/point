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
            'customer_name' => 'required_without:customer_id',
            'group_name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'similar_product' => 'required',
            'payment_method' => 'required',
        ];
    }
}
