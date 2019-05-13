<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeEmail;
use App\Model\HumanResource\Employee\EmployeeGroup;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeSocialMedia;
use App\Model\HumanResource\Employee\EmployeeSalaryHistory;

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

        $employeeGroup = new EmployeeGroup;
        $employeeGroup->name = 'Dummy Company';
        $employeeGroup->save();

        $employee = new Employee;
        $employee->name = 'John Doe';
        $employee->personal_identity = 'PASSPORT 940001930211FA';
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
