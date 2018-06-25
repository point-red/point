<?php

namespace App\Http\Resources\HumanResource\Employee\Employee;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'code' => $this->person->code,
            'name' => $this->person->name,
            'personal_identity' => $this->person->personal_identity,
            'last_education' => $this->last_education,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
        ];
    }
}
