<?php

use Illuminate\Database\Seeder;

class DummyTenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Kpi
        $this->call(DummyKpiTemplateSeeder::class);
        $this->call(DummyEmployeeSeeder::class);
    }
}
