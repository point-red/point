<?php

namespace App\Http\Requests\Master\Group;

use App\Model\Master\Group;
use App\Helpers\Master\GroupClassReference;
use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
                function ($attribute, $value, $fail) {
                    $classReference = GroupClassReference::isAvailable($this->class_reference);
                    if (Group::where('name', $value)->where('class_reference', $classReference)->count() > 0) {
                        $fail($attribute.' is already exists.');
                    }
                },
            ],
        ];
    }
}
