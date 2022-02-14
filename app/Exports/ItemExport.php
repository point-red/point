<?php

namespace App\Exports;

use App\Model\Master\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemExport implements FromCollection,  WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Item::all();
    }

    public function map($item): array
    {
        return [
            $item->code,
            $item->name,
            $item->relatedChartOfAccount->name,
            $item->stock,
            $item->stock_reminder,
            $item->sub_ledger,
            $item->position,
            $item->taxable,
            $item->require_expiry_date,
            $item->require_production_number,
            $item->unit_default_purchase,
            $item->unit_default_sales,
            $item->unit_default,
        ];
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Item Name',
            'Chart of Account',
            'Unit Of Converter 1',
            'Unit Of Converter 2',
            'Converter',
            'Unit Of Converter 3',
            'Converter',
            'Expiry Date',
            'Production Number',
            'Default Purchase',
            'Default Sales',
            'Group' 
        ];
    }
}
