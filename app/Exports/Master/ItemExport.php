<?php

namespace App\Exports\Master;

use App\Model\Master\Item;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemExport implements FromView, ShouldAutoSize
{
    /**
     * @return array[]
     */
    public function heading(): array
    {
        return [
            'Item Code',
            'Item name',
            'Chart of Account',
            'Uom',
            'Converter',
            'Unit of Converter 2',
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

    /**
     * @return mixed
     */
    public function getData()
    {
        return Item::from(Item::getTableName() . ' as ' . Item::$alias)
            ->with('units')
            ->get()
            ->map(function ($row) {
                $data = [
                    $row->code,
                    $row->name,
                    $row->account->name,
                ];

                foreach ($row->units->take(3) as $key => $datas) {
                    if ($key == 0) {
                        $data[] = $datas->name;
                    } else {
                        $data[] = (int)$datas->converter;
                    }
                    $data[] = $datas->name;
                }
                $data[] = $row->require_expiry_date == 1 ? 'true' : 'false';
                $data[] = $row->require_production_number == 1 ? 'true' : 'false';
                $data[] = $row->units->where('id', $row->unit_default_purchase)->first()->name ?? '';
                $data[] = $row->units->where('id', $row->unit_default_sales)->first()->name ?? '';
                $data[] = $row->units->where('id', $row->unit_default)->first()->name ?? '';

                return $data;
            });
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('exports.master.item.exportToExcel', [
            'heading' => $this->heading(),
            'item' => $this->getData()
        ]);
    }
}
