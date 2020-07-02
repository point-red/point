<?php

namespace App\Imports\Template;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\PriceListItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $coa = ChartOfAccount::where('name', 'FINISHED GOOD INVENTORY')->first()->id;
        foreach ($collection as $row) {
            if (Item::where('code', $row['item_code'])->orWhere('name', $row['item_name'])->count() === 0) {
                $item = new Item;
                $item->code = $row['item_code'];
                $item->name = $row['item_name'];
                $item->chart_of_account_id = $coa;
                $item->unit_default = 1;
                $item->save();
    
                $unit = new ItemUnit();
                $unit->label = $row['unit'];
                $unit->name = $row['unit'];
                $unit->item_id = $item->id;
                $unit->save();
    
                $price = new PriceListItem();
                $price->item_unit_id = $unit->id;
                $price->pricing_group_id = 1;
                $price->price = str_replace(',', '', $row['price']);
                $price->save();
            }
        }
    }
}
