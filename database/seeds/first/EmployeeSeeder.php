<?php

use Illuminate\Database\Seeder;
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
        $religions = ['Christian', 'Catholic', 'Islam', 'Buddha', 'Hindu'];
        for ($i = 0; $i < count($religions); $i++) {
            DB::connection('tenant')->table('employee_religions')->insert([
                'name' => $religions[$i]
            ]);
        }

        $maritalStatues = ['Single', 'Married'];
        for ($i = 0; $i < count($maritalStatues); $i++) {
            DB::connection('tenant')->table('employee_marital_statuses')->insert([
                'name' => $maritalStatues[$i]
            ]);
        }

        $genders = ['Male', 'Female'];
        for ($i = 0; $i < count($genders); $i++) {
            DB::connection('tenant')->table('employee_genders')->insert([
                'name' => $genders[$i]
            ]);
        }

        $statuses = ['OS', 'NON OS'];
        for ($i = 0; $i < count($statuses); $i++) {
            DB::connection('tenant')->table('employee_statuses')->insert([
                'name' => $statuses[$i]
            ]);
        }

        $jobLocations = ['Surabaya', 'Sidoarjo', 'Gresik', 'Situbondo', 'Banyuwangi', 'Bojonegoro', 'Jember', 'Malang', 'Mojokerto', 'Gorontalo', 'Lamongan', 'Blitar', 'Kediri'];
        for ($i = 0; $i < count($jobLocations); $i++) {
            DB::connection('tenant')->table('employee_job_locations')->insert([
                'name' => $jobLocations[$i]
            ]);
        }
    }
}
