<?php

namespace App\Http\Requests\Psychotest\KraepelinColumn;

use Illuminate\Foundation\Http\FormRequest;

class StoreKraepelinColumnRequest extends FormRequest
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
            'kraepelin_id' => ['required', 'numeric']
        ];
    }
}
