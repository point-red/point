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
            'employee_group_id' => $this->employee_group_id,
            'group' => $this->group, // relationship with EmployeeGroup
            'code' => $this->person->code,
            'name' => $this->name,
            'addresses' => $this->person->addresses,
            'emails' => $this->person->emails,
            'phones' => $this->person->phones,
            'social_media' => $this->socialMedia,
            'personal_identity' => $this->person->personal_identity,
            'last_education' => $this->last_education,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'employee_gender_id' => $this->employee_gender_id,
            'gender' => $this->gender, // relationship with EmployeeGender
            'employee_marital_status_id' => $this->employee_marital_status_id,
            'marital_status' => $this->maritalStatus, // relationship with EmployeeMaritalStatus
            'married_with' => $this->married_with,
            'employee_religion_id' => $this->employee_religion_id,
            'religion' => $this->religion, // relationship with EmployeeReligion
            'join_date' => $this->join_date,
            'job_title' => $this->job_title ?? '',
            'company_emails' => $this->companyEmails,
            'contracts' => $this->contracts,
            'salary_histories' => $this->salaryHistories,
            'scorers' => $this->users,
            'kpi_template_id' => $this->kpi_template_id,
        ];
    }
}
