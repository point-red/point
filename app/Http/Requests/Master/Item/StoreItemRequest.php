<?php

namespace App\Http\Requests\Master\Item;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @throws \Exception
     */
    public function authorize()
    {
        if (! tenant(auth()->user()->id)->hasPermissionTo('create item')) {
            return false;
        }

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
            'units' => 'required|array',
            'groups' => 'array',
        ];
    }
}
