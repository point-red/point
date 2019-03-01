<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeScorer;
use App\Model\HumanResource\Employee\EmployeeGroup;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeEmail;
use App\Model\HumanResource\Employee\EmployeeSalaryHistory;
use App\Model\HumanResource\Employee\EmployeeSocialMedia;
use App\Http\Requests\HumanResource\Employee\Employee\StoreEmployeeRequest;
use App\Http\Requests\HumanResource\Employee\Employee\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function index(Request $request)
    {
        $employees = Employee::eloquentFilter($request)
            ->with('group')
            ->with('gender')
            ->with('religion')
            ->with('maritalStatus')
            ->with('companyEmails')
            ->with('socialMedia')
            ->with('contracts')
            ->with('salaryHistories')
            ->with('kpiTemplate')
            ->with('scorers')
            ->with('emails')
            ->with('addresses')
            ->with('phones')
            ->select('employees.*')
            ->paginate($request->get('paginate') ?? 20);

        $additional = [];
        foreach (explode(',', $request->get('additional')) as $addition) {
            if ($addition == 'groups') {
                $additional = $additional + ['groups' => EmployeeGroup::all()];
            }
        }

        return (new ApiCollection($employees))
            ->additional(['additional' => $additional]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\Employee\StoreEmployeeRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function store(StoreEmployeeRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $employee = new Employee;
        $employee->code = $request->get('code');
        $employee->name = $request->get('name');
        $employee->personal_identity = $request->get('personal_identity');
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

        for ($i = 0; $i < count($request->get('addresses')); $i++) {
            $employeeAddress = new Employee\EmployeeAddress;
            $employeeAddress->employee_id = $employee->id;
            $employeeAddress->address = $request->get('addresses')[$i]['address'];
            $employeeAddress->save();
        }

        for ($i = 0; $i < count($request->get('phones')); $i++) {
            $employeePhone = new Employee\EmployeePhone;
            $employeePhone->employee_id = $employee->id;
            $employeePhone->phone = $request->get('phones')[$i]['phone'];
            $employeePhone->save();
        }

        for ($i = 0; $i < count($request->get('emails')); $i++) {
            $employeeEmail = new EmployeeEmail;
            $employeeEmail->employee_id = $employee->id;
            $employeeEmail->email = $request->get('emails')[$i]['email'];
            $employeeEmail->save();
        }

        for ($i = 0; $i < count($request->get('company_emails')); $i++) {
            $employeeEmails = new Employee\EmployeeCompanyEmail;
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

        return new ApiResource($employee);
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int                     $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function show(Request $request, $id)
    {
        $employee = Employee::eloquentFilter($request)
            ->where('employees.id', $id)
            ->with('group')
            ->with('gender')
            ->with('religion')
            ->with('maritalStatus')
            ->with('companyEmails')
            ->with('socialMedia')
            ->with('contracts')
            ->with('salaryHistories')
            ->with('kpiTemplate')
            ->with('scorers')
            ->with('emails')
            ->with('addresses')
            ->with('phones')
            ->select('employees.*')
            ->first();

        return new ApiResource($employee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\Employee\UpdateEmployeeRequest $request
     * @param  int                                                                     $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $employee = Employee::findOrFail($id);
        $employee->code = $request->get('code');
        $employee->name = $request->get('name');
        $employee->personal_identity = $request->get('personal_identity');
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

        $deleteAddresses = array_column($request->get('addresses'), 'id');
        Employee\EmployeeAddress::where('employee_id', $employee->id)->whereNotIn('id', $deleteAddresses)->delete();
        for ($i = 0; $i < count($request->get('addresses')); $i++) {
            if (isset($request->get('addresses')[$i]['id'])) {
                $employeeAddress = Employee\EmployeeAddress::findOrFail($request->get('addresses')[$i]['id']);
            } else {
                $employeeAddress = new Employee\EmployeeAddress;
                $employeeAddress->employee_id = $employee->id;
            }
            $employeeAddress->address = $request->get('addresses')[$i]['address'];
            $employeeAddress->save();
        }
        $deletePhones = array_column($request->get('phones'), 'id');
        Employee\EmployeePhone::where('employee_id', $employee->id)->whereNotIn('id', $deletePhones)->delete();
        for ($i = 0; $i < count($request->get('phones')); $i++) {
            if (isset($request->get('phones')[$i]['id'])) {
                $employeePhone = Employee\EmployeePhone::findOrFail($request->get('phones')[$i]['id']);
            } else {
                $employeePhone = new Employee\EmployeePhone;
                $employeePhone->employee_id = $employee->id;
            }
            $employeePhone->phone = $request->get('phones')[$i]['phone'];
            $employeePhone->save();
        }
        $deleted = array_column($request->get('company_emails'), 'id');
        Employee\EmployeeCompanyEmail::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('company_emails')); $i++) {
            if (isset($request->get('company_emails')[$i]['id'])) {
                $employeeCompanyEmail = Employee\EmployeeCompanyEmail::findOrFail($request->get('company_emails')[$i]['id']);
            } else {
                $employeeCompanyEmail = new Employee\EmployeeCompanyEmail;
                $employeeCompanyEmail->employee_id = $employee->id;
            }
            $employeeCompanyEmail->email = $request->get('company_emails')[$i]['email'];
            $employeeCompanyEmail->save();
        }
        $deleted = array_column($request->get('emails'), 'id');
        EmployeeEmail::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('emails')); $i++) {
            if (isset($request->get('emails')[$i]['id'])) {
                $employeeEmails = EmployeeEmail::findOrFail($request->get('emails')[$i]['id']);
            } else {
                $employeeEmails = new EmployeeEmail;
                $employeeEmails->employee_id = $employee->id;
            }
            $employeeEmails->email = $request->get('emails')[$i]['email'];
            $employeeEmails->save();
        }
        $deleted = array_column($request->get('salary_histories'), 'id');
        EmployeeSalaryHistory::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
        for ($i = 0; $i < count($request->get('salary_histories')); $i++) {
            if (isset($request->get('salary_histories')[$i]['id'])) {
                $employeeSalaryHistory = EmployeeSalaryHistory::findOrFail($request->get('salary_histories')[$i]['id']);
            } else {
                $employeeSalaryHistory = new EmployeeSalaryHistory;
                $employeeSalaryHistory->employee_id = $employee->id;
            }
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
                $employeeSocialMedia->employee_id = $employee->id;
            }
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
                $employeeContract->employee_id = $employee->id;
            }
            $employeeContract->contract_begin = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_begin']));
            $employeeContract->contract_end = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_end']));
            $employeeContract->link = '';
            $employeeContract->notes = $request->get('contracts')[$i]['notes'];
            $employeeContract->save();
        }

        $scorers = $request->get('scorers');
        $deleted = array_column($request->get('scorers'), 'id');
        EmployeeScorer::where('employee_id', $employee->id)->whereNotIn('user_id', $deleted)->delete();
        foreach ($scorers as $scorer) {
            if (! $employee->scorers->contains($scorer['id'])) {
                $employee->scorers()->attach($scorer['id']);
            }
        }
        DB::connection('tenant')->commit();

        return new ApiResource($employee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        $employee->delete();

        return new ApiResource($employee);
    }
}
