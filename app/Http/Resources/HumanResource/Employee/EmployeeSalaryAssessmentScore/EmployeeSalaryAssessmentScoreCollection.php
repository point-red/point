<?php
namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAssessmentScore;

use App\Model\HumanResource\Employee\EmployeeSalaryAssessmentScore;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeSalaryAssessmentScoreCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeSalaryAssessmentScore $salaryAssessmentScore) {
            return new EmployeeSalaryAssessmentScoreResource($salaryAssessmentScore);
        });
        
        return parent::toArray($request);
    }
}