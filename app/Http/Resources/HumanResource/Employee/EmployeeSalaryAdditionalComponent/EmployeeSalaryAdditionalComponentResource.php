<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAdditionalComponent;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryAdditionalComponentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'automated_code' => $this->automated_code,
            'automated_code_name' => $this->automated_code_name,
        ];
    }
}
