<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAssessment;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Employee\EmployeeSalaryAssessmentScore\EmployeeSalaryAssessmentScoreResource;
use App\Http\Resources\HumanResource\Employee\EmployeeSalaryAssessmentTarget\EmployeeSalaryAssessmentTargetResource;

class EmployeeSalaryAssessmentResource extends JsonResource
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
            'salary_id' => $this->salary_id,
            'name' => $this->name,
            'weight' => $this->weight,
            'scores' => EmployeeSalaryAssessmentScoreResource::collection($this->scores),
            'targets' => EmployeeSalaryAssessmentTargetResource::collection($this->targets),
        ];
    }
}
