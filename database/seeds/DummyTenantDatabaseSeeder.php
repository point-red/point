<?php

use App\Model\Master\Person;
use App\Model\Master\Warehouse;
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
        factory(Warehouse::class, 10)->create();
        factory(Person::class, 10)->create();
    }
}
