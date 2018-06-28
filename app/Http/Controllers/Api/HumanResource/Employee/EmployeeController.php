<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Model\Master\Person;
use Illuminate\Http\Request;
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

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeCollection
     */
    public function index()
    {
        return new EmployeeCollection(Employee::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\HumanResource\Employee\Employee\EmployeeResource
     */
    public function store(Request $request)
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
        $employee->birth_date = date('Y-m-d', strtotime($request->get('birth_date')));
        $employee->birth_place = $request->get('birth_place');
        $employee->gender = $request->get('gender');
        $employee->marital_status = $request->get('marital_status');
        $employee->married_with = $request->get('married_with');
        $employee->religion = $request->get('religion');
        $employee->employee_group_id = $request->get('employee_group_id');
        $employee->join_date = date('Y-m-d', strtotime($request->get('join_date')));
        $employee->job_title = $request->get('job_title');
        $employee->save();

        for ($i = 0; $i < count($request->get('email_companies')); $i++) {
            $employeeEmails = new EmployeeEmail;
            $employeeEmails->employee_id = $employee->id;
            $employeeEmails->email = $request->get('email_companies')[$i]['email'];
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
            $employeeContract->contract_begin = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_date']));
            $employeeContract->contract_end = date('Y-m-d', strtotime($request->get('contracts')[$i]['expired_date']));
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
