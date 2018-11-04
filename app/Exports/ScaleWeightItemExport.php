<?php

namespace App\Exports;

use App\Model\Plugin\ScaleWeight\ScaleWeightItem;
use Maatwebsite\Excel\Concerns\FromCollection;

class ScaleWeightItemExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ScaleWeightItem::all();
    }
}
