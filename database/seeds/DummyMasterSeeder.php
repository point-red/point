<?php

use Illuminate\Database\Seeder;

class DummyMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Model\Master\Customer::class, 10)->create();
        factory(\App\Model\Master\Supplier::class, 10)->create();
        factory(\App\Model\Master\Warehouse::class, 2)->create();
    }
}
