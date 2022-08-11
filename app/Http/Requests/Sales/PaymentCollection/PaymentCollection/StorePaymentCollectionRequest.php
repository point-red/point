<?php

namespace App\Http\Requests\Sales\PaymentCollection\PaymentCollection;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StorePaymentCollectionRequest extends FormRequest
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
        $rulesForm = ValidationRule::form();
        $rulesPaymentCollection = [
            'payment_type' => 'required|string'
        ];
        return array_merge($rulesForm, $rulesPaymentCollection);
    }
}
