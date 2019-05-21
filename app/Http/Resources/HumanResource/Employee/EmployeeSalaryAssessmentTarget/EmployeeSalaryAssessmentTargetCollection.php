<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAssessmentTarget;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentTarget;

class EmployeeSalaryAssessmentTargetCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeSalaryAssessmentTarget $salaryAssessmentTarget) {
            return new EmployeeSalaryAssessmentTargetResource($salaryAssessmentTarget);
        });

        return parent::toArray($request);
    }
}
