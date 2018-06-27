<?php

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeEmail;
use App\Model\HumanResource\Employee\EmployeeGroup;
use App\Model\HumanResource\Employee\EmployeeSalaryHistory;
use App\Model\HumanResource\Employee\EmployeeSocialMedia;
use App\Model\Master\Person;
use App\Model\Master\PersonAddress;
use App\Model\Master\PersonEmail;
use App\Model\Master\PersonPhone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('tenant')->beginTransaction();

        $person = new Person;
        $person->code = null;
        $person->name = 'John Doe';
        $person->personal_identity = 'PASSPORT 940001930211FA';
        $person->save();

        $personAddress = new PersonAddress;
        $personAddress->person_id = $person->id;
        $personAddress->address = '21th Street LA';
        $personAddress->save();

        $personPhone = new PersonPhone;
        $personPhone->person_id = $person->id;
        $personPhone->phone = '+1-0123-1234';
        $personPhone->save();

        $personEmail = new PersonEmail;
        $personEmail->person_id = $person->id;
        $personEmail->email = 'john.doe@doe.com';
        $personEmail->save();

        $employeeGroup = new EmployeeGroup;
        $employeeGroup->name = 'Dummy Company';
        $employeeGroup->save();

        $employee = new Employee;
        $employee->person_id = $person->id;
        $employee->last_education = '';
        $employee->birth_date = now();
        $employee->birth_place = '';
        $employee->gender = 'Male';
        $employee->marital_status = 'Single';
        $employee->married_with = null;
        $employee->religion = 'Christian';
        $employee->employee_group_id = $employeeGroup->id;
        $employee->join_date = now();
        $employee->job_title = 'Manager';
        $employee->kpi_template_id = 1;
        $employee->save();

        $employeeEmails = new EmployeeEmail;
        $employeeEmails->employee_id = $employee->id;
        $employeeEmails->email = 'john.doe@company.com';
        $employeeEmails->save();

        $employeeSalaryHistory = new EmployeeSalaryHistory;
        $employeeSalaryHistory->employee_id = $employee->id;
        $employeeSalaryHistory->date = now();
        $employeeSalaryHistory->salary = 2000;
        $employeeSalaryHistory->save();

        $employeeSocialMedia = new EmployeeSocialMedia;
        $employeeSocialMedia->employee_id = $employee->id;
        $employeeSocialMedia->type = 'Facebook';
        $employeeSocialMedia->account = 'John Doe';
        $employeeSocialMedia->save();

        $employeeContract = new EmployeeContract;
        $employeeContract->employee_id = $employee->id;
        $employeeContract->contract_begin = now();
        $employeeContract->contract_end = now();
        $employeeContract->link = '';
        $employeeContract->notes = 'Trial Contract';
        $employeeContract->save();

        DB::connection('tenant')->commit();
    }
}
