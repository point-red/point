<?php

namespace App\Exports\PinPoint\Performance;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PerformanceExport implements WithMultipleSheets
{
    /**
     * ScaleWeightItemExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $days = date('t', strtotime($this->dateFrom));

        for ($i = 1; $i <= $days; $i++) {
            $dateFrom = date('Y-m-'.$i.' 00:00:00', strtotime($this->dateFrom));
            $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($this->dateTo));
            $sheets[] = new DailySheet($i, $dateFrom, $dateTo);
        }

        return $sheets;
    }
}
