<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAssessment;

use App\Model\HumanResource\Employee\EmployeeSalaryAssessment;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeSalaryAssessmentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeSalaryAssessment $salaryAssessment) {
            return new EmployeeSalaryAssessmentResource($salaryAssessment);
        });

        return parent::toArray($request);
    }
}
