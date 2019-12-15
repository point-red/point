<?php

use App\Model\Master\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('tenant')->beginTransaction();

        $item = new Item;
        $item->name = 'Item ABC';
        $item->save();

        DB::connection('tenant')->commit();
    }
}
