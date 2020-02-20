<?php

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(ChartOfAccountTypeSeeder::class);
    }
}
