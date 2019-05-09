<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Plugin\ScaleWeight\ScaleWeightTruck;

class ScaleWeightTruckExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * ScaleWeightTruckExport constructor.
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
        return ScaleWeightTruck::query()->whereBetween('time_in', [$this->dateFrom, $this->dateTo]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date In',
            'Time In',
            'Date Out',
            'Time Out',
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
            date('Y-m-d', strtotime($row->time_in)),
            date('H:i', strtotime($row->time_in)),
            date('Y-m-d', strtotime($row->time_out)),
            date('H:i', strtotime($row->time_out)),
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
