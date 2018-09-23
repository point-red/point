<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Model\Master\Person;
use App\Model\Master\PersonEmail;
use App\Model\Master\PersonPhone;
use Illuminate\Support\Facades\DB;
use App\Model\Master\PersonAddress;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeEmail;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeSocialMedia;
use App\Model\HumanResource\Employee\EmployeeSalaryHistory;
use App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource;
use App\Http\Resources\HumanResource\Employee\Employee\EmployeeCollection;
use App\Http\Requests\HumanResource\Employee\Employee\StoreEmployeeRequest;
use App\Http\Requests\HumanResource\Employee\Employee\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeCollection
     */
    public function index()
    {
        return new EmployeeCollection(Employee::join('persons', 'persons.id', '=', 'employees.person_id')->select('employees.*', 'persons.name as name')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource
     */
    public function store(StoreEmployeeRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $person = new Person;
        $person->code = $request->get('code');
        $person->name = $request->get('name');
        $person->personal_identity = $request->get('personal_identity');
        $person->save();

        for ($i = 0; $i < count($request->get('addresses')); $i++) {
            $personAddress = new PersonAddress;
            $personAddress->person_id = $person->id;
            $personAddress->address = $request->get('addresses')[$i]['address'];
            $personAddress->save();
        }

        for ($i = 0; $i < count($request->get('phones')); $i++) {
            $personPhone = new PersonPhone;
            $personPhone->person_id = $person->id;
            $personPhone->phone = $request->get('phones')[$i]['phone'];
            $personPhone->save();
        }

        for ($i = 0; $i < count($request->get('emails')); $i++) {
            $personEmail = new PersonEmail;
            $personEmail->person_id = $person->id;
            $personEmail->email = $request->get('emails')[$i]['email'];
            $personEmail->save();
        }

        $employee = new Employee;
        $employee->person_id = $person->id;
        $employee->last_education = $request->get('last_education');
        $employee->birth_date = $request->get('birth_date') ? date('Y-m-d', strtotime($request->get('birth_date'))) : null;
        $employee->birth_place = $request->get('birth_place');
        $employee->employee_gender_id = $request->get('employee_gender_id');
        $employee->employee_marital_status_id = $request->get('employee_marital_status_id');
        $employee->married_with = $request->get('married_with');
        $employee->employee_religion_id = $request->get('employee_religion_id');
        $employee->employee_group_id = $request->get('employee_group_id');
        $employee->join_date = $request->get('join_date') ? date('Y-m-d', strtotime($request->get('join_date'))) : null;
        $employee->job_title = $request->get('job_title');
        $employee->save();

        for ($i = 0; $i < count($request->get('company_emails')); $i++) {
            $employeeEmails = new EmployeeEmail;
            $employeeEmails->employee_id = $employee->id;
            $employeeEmails->email = $request->get('company_emails')[$i]['email'];
            $employeeEmails->save();
        }

        for ($i = 0; $i < count($request->get('salary_histories')); $i++) {
            $employeeSalaryHistory = new EmployeeSalaryHistory;
            $employeeSalaryHistory->employee_id = $employee->id;
            $employeeSalaryHistory->date = date('Y-m-d', strtotime($request->get('salary_histories')[$i]['date']));
            $employeeSalaryHistory->salary = $request->get('salary_histories')[$i]['salary'];
            $employeeSalaryHistory->save();
        }

        for ($i = 0; $i < count($request->get('social_media')); $i++) {
            $employeeSocialMedia = new EmployeeSocialMedia;
            $employeeSocialMedia->employee_id = $employee->id;
            $employeeSocialMedia->type = $request->get('social_media')[$i]['type'];
            $employeeSocialMedia->account = $request->get('social_media')[$i]['account'];
            $employeeSocialMedia->save();
        }

        for ($i = 0; $i < count($request->get('contracts')); $i++) {
            $employeeContract = new EmployeeContract;
            $employeeContract->employee_id = $employee->id;
            $employeeContract->contract_begin = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_begin']));
            $employeeContract->contract_end = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_end']));
            $employeeContract->link = '';
            $employeeContract->notes = $request->get('contracts')[$i]['notes'];
            $employeeContract->save();
        }

        DB::connection('tenant')->commit();

        return new EmployeeResource($employee);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource
     */
    public function show($id)
    {
        return new EmployeeResource(Employee::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $employee = Employee::findOrFail($id);
        $employee->last_education = $request->get('last_education');
        $employee->birth_date = $request->get('birth_date') ? date('Y-m-d', strtotime($request->get('birth_date'))) : null;
        $employee->birth_place = $request->get('birth_place');
        $employee->employee_gender_id = $request->get('employee_gender_id');
        $employee->employee_marital_status_id = $request->get('employee_marital_status_id');
        $employee->married_with = $request->get('married_with');
        $employee->employee_religion_id = $request->get('employee_religion_id');
        $employee->employee_group_id = $request->get('employee_group_id');
        $employee->join_date = $request->get('join_date') ? date('Y-m-d', strtotime($request->get('join_date'))) : null;
        $employee->job_title = $request->get('job_title');
        $employee->save();

        $person = Person::findOrFail($employee->person_id);
        $person->code = $request->get('code');
        $person->name = $request->get('name');
        $person->personal_identity = $request->get('personal_identity');
        $person->save();

        $deleteAddresses = array_column($request->get('addresses'), 'id');
        PersonAddress::where('person_id', $person->id)->whereNotIn('id', $deleteAddresses)->delete();
        for ($i = 0; $i < count($request->get('addresses')); $i++) {
            if (isset($request->get('addresses')[$i]['id'])) {
                $personAddress = PersonAddress::findOrFail($request->get('addresses')[$i]['id']);
            } else {
                $personAddress = new PersonAddress;
                $personAddress->person_id = $person->id;
            }
            $personAddress->address = $request->get('addresses')[$i]['address'];
            $personAddress->save();
        }

        $deletePhones = array_column($request->get('phones'), 'id');
        PersonPhone::where('person_id', $person->id)->whereNotIn('id', $deletePhones)->delete();
        for ($i = 0; $i < count($request->get('phones')); $i++) {
            if (isset($request->get('phones')[$i]['id'])) {
                $personPhone = PersonPhone::findOrFail($request->get('phones')[$i]['id']);
            } else {
                $personPhone = new PersonPhone;
            }
            $personPhone->person_id = $person->id;
            $personPhone->phone = $request->get('phones')[$i]['phone'];
            $personPhone->save();
        }

        $deleted = array_column($request->get('emails'), 'id');
        PersonEmail::where('person_id', $person->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('emails')); $i++) {
            if (isset($request->get('emails')[$i]['id'])) {
                $personEmail = PersonEmail::findOrFail($request->get('emails')[$i]['id']);
            } else {
                $personEmail = new PersonEmail;
            }
            $personEmail->person_id = $person->id;
            $personEmail->email = $request->get('emails')[$i]['email'];
            $personEmail->save();
        }

        $deleted = array_column($request->get('company_emails'), 'id');
        EmployeeEmail::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('company_emails')); $i++) {
            if (isset($request->get('company_emails')[$i]['id'])) {
                $employeeEmails = EmployeeEmail::findOrFail($request->get('company_emails')[$i]['id']);
            } else {
                $employeeEmails = new EmployeeEmail;
            }
            $employeeEmails->employee_id = $employee->id;
            $employeeEmails->email = $request->get('company_emails')[$i]['email'];
            $employeeEmails->save();
        }

        $deleted = array_column($request->get('salary_histories'), 'id');
        EmployeeSalaryHistory::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('salary_histories')); $i++) {
            if (isset($request->get('salary_histories')[$i]['id'])) {
                $employeeSalaryHistory = EmployeeSalaryHistory::findOrFail($request->get('salary_histories')[$i]['id']);
            } else {
                $employeeSalaryHistory = new EmployeeSalaryHistory;
            }
            $employeeSalaryHistory->employee_id = $employee->id;
            $employeeSalaryHistory->date = date('Y-m-d', strtotime($request->get('salary_histories')[$i]['date']));
            $employeeSalaryHistory->salary = $request->get('salary_histories')[$i]['salary'];
            $employeeSalaryHistory->save();
        }

        $deleted = array_column($request->get('social_media'), 'id');
        EmployeeSocialMedia::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('social_media')); $i++) {
            if (isset($request->get('social_media')[$i]['id'])) {
                $employeeSocialMedia = EmployeeSocialMedia::findOrFail($request->get('social_media')[$i]['id']);
            } else {
                $employeeSocialMedia = new EmployeeSocialMedia;
            }
            $employeeSocialMedia->employee_id = $employee->id;
            $employeeSocialMedia->type = $request->get('social_media')[$i]['type'];
            $employeeSocialMedia->account = $request->get('social_media')[$i]['account'];
            $employeeSocialMedia->save();
        }

        $deleted = array_column($request->get('contracts'), 'id');
        EmployeeContract::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('contracts')); $i++) {
            if (isset($request->get('contracts')[$i]['id'])) {
                $employeeContract = EmployeeContract::findOrFail($request->get('contracts')[$i]['id']);
            } else {
                $employeeContract = new EmployeeContract;
            }
            $employeeContract->employee_id = $employee->id;
            $employeeContract->contract_begin = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_begin']));
            $employeeContract->contract_end = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_end']));
            $employeeContract->link = '';
            $employeeContract->notes = $request->get('contracts')[$i]['notes'];
            $employeeContract->save();
        }

        DB::connection('tenant')->commit();

        return new EmployeeResource($employee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        $employee->delete();

        return new EmployeeResource($employee);
    }
}
