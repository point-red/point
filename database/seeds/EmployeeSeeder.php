<?php

use Illuminate\Database\Seeder;
use App\Model\HumanResource\Employee\EmployeeGender;
use App\Model\HumanResource\Employee\EmployeeReligion;
use App\Model\HumanResource\Employee\EmployeeMaritalStatus;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $religions = ['Christian', 'Catholic', 'Islam', 'Buddha', 'Hindu'];
        for ($i = 0; $i < count($religions); $i++) {
            $employeeReligion = new EmployeeReligion;
            $employeeReligion->name = $religions[$i];
            $employeeReligion->save();
        }

        $maritalStatues = ['Single', 'Married'];
        for ($i = 0; $i < count($maritalStatues); $i++) {
            $employeeMaritalStatus = new EmployeeMaritalStatus;
            $employeeMaritalStatus->name = $maritalStatues[$i];
            $employeeMaritalStatus->save();
        }

        $genders = ['Male', 'Female'];
        for ($i = 0; $i < count($genders); $i++) {
            $employeeGender = new EmployeeGender;
            $employeeGender->name = $genders[$i];
            $employeeGender->save();
        }
    }
}
