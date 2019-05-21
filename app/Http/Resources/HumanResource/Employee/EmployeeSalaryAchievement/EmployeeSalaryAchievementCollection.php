<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAchievement;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Model\HumanResource\Employee\EmployeeSalaryAchievement;

class EmployeeSalaryAchievementCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (EmployeeSalaryAchievement $salaryAchievement) {
            return new EmployeeSalaryAchievementResource($salaryAchievement);
        });

        return parent::toArray($request);
    }
}
