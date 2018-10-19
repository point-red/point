<?php

use Illuminate\Database\Seeder;
use App\Model\HumanResource\Employee\EmployeeGender;
use App\Model\HumanResource\Employee\EmployeeReligion;
use App\Model\HumanResource\Employee\EmployeeMaritalStatus;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $owner = \App\Model\Master\User::first();

        $religions = ['Christian', 'Catholic', 'Islam', 'Buddha', 'Hindu'];
        for ($i = 0; $i < count($religions); $i++) {
            DB::table('employee_religions')->insert([
                'name' => $religions[$i],
                'created_by' => $owner->id,
                'updated_by' => $owner->id,
            ]);
        }

        $maritalStatues = ['Single', 'Married'];
        for ($i = 0; $i < count($maritalStatues); $i++) {
            DB::table('employee_marital_statuses')->insert([
                'name' => $maritalStatues[$i],
                'created_by' => $owner->id,
                'updated_by' => $owner->id,
            ]);
        }

        $genders = ['Male', 'Female'];
        for ($i = 0; $i < count($genders); $i++) {
            DB::table('employee_genders')->insert([
                'name' => $genders[$i],
                'created_by' => $owner->id,
                'updated_by' => $owner->id,
            ]);
        }
    }
}
