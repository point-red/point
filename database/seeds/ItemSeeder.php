<?php

use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Model\Master\Item::class, 10)
            ->create()
            ->each(function ($item) {
                $item->units()->save(factory(\App\Model\Master\ItemUnit::class)->make());
            });
    }
}
