<?php

namespace App\Http\Requests\Master\Item;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ValidationRule;

class StoreManyItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'items.*.chart_of_account_id' => ValidationRule::foreignKey('chart_of_accounts'),
            'items.*.units' => 'required|array',
            'items.*.groups' => 'nullable|array',

            // TODO each units and groups fields
        ];
    }
}
