<?php

use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Model\Master\PricingGroup::class, 2)->create();

        foreach (\App\Model\Master\PricingGroup::all() as $pricingGroup) {
            foreach (\App\Model\Master\ItemUnit::all() as $itemUnit) {
                $priceList = new \App\Model\Master\PriceListItem;
                $priceList->pricing_group_id = $pricingGroup->id;
                $priceList->item_unit_id = $itemUnit->id;
                $priceList->price = rand(1, 9) * 1000;
                $priceList->date = now();
                $priceList->save();
            }
        }
    }
}
