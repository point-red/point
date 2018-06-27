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
            'addresses' => $this->person->addresses,
            'emails' => $this->person->emails,
            'phones' => $this->person->phones,
            'social_media' => $this->socialMedia,
            'personal_identity' => $this->person->personal_identity,
            'last_education' => $this->last_education,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'married_with' => $this->married_with,
            'religion' => $this->religion,
            'employee_group' => $this->group,
            'join_date' => $this->join_date,
            'job_title' => $this->job_title,
            'company_emails' => $this->companyEmails,
            'contracts' => $this->contracts,
            'salary_histories' => $this->salaryHistories,
            'kpi_template_id' => $this->kpi_template_id
        ];
    }
}
