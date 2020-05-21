<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalaryAchievement;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryAchievementResource extends JsonResource
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
            'week1' => $this->week1,
            'week2' => $this->week2,
            'week3' => $this->week3,
            'week4' => $this->week4,
            'week5' => $this->week5,
        ];
    }
}
