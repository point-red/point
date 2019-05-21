<?php

namespace App\Http\Requests\Master\Group;

use Illuminate\Validation\Rule;
use App\Helpers\Master\GroupClassReference;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
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
            'name' => [
                'required',
                Rule::unique('tenant.groups')->where(function ($query) {
                    return $query->where('name', $this->name)->where('class_reference', $this->class_reference);
                })->ignore($this->id),
            ],
            'class_reference' => function ($attribute, $value, $fail) {
                if (! GroupClassReference::isAvailable($this->class_reference)) {
                    $fail($attribute.' is not valid');
                }
            },
        ];
    }
}
