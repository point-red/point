<?php

use Illuminate\Database\Seeder;
use App\Model\Master\Warehouse;

class DistributionWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $warehouse = new Warehouse;
        $warehouse->branch_id = 1;
        $warehouse->name = 'DISTRIBUTION WAREHOUSE';
        $warehouse->save();
    }
}
