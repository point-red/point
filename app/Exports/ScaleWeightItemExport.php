<?php

namespace App\Exports;

use App\Model\Plugin\ScaleWeight\ScaleWeightItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ScaleWeightItemExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * ScaleWeightItemExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return ScaleWeightItem::query()->whereBetween('time', [$this->dateFrom, $this->dateTo]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date',
            'Time',
            'Machine',
            'Form Number',
            'Vendor',
            'Driver',
            'License Number',
            'Item',
            'Gross',
            'Tare',
            'Net',
            'User',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            date('Y-m-d', strtotime($row->time)),
            date('H:i', strtotime($row->time)),
            $row->machine_code,
            $row->form_number,
            $row->vendor,
            $row->driver,
            $row->license_number,
            $row->item,
            $row->gross_weight,
            $row->tare_weight,
            $row->net_weight,
            $row->user,
        ];
    }
}
