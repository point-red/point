<?php

namespace App\Exports;

use App\Model\Plugin\ScaleWeight\ScaleWeightTruck;
use Maatwebsite\Excel\Concerns\FromCollection;

class ScaleWeightTruckExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ScaleWeightTruck::all();
    }
}
