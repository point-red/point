<?php

namespace App\Exports\PinPoint\Performance;

use Carbon\Carbon;
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
        $this->dateFrom = date('Y-m-d H:i:s', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d H:i:s', strtotime($dateTo));
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

        $date = Carbon::parse(date('Y-m-01 00:00:00', strtotime($this->dateFrom)));
        $months = $date->daysInMonth;
        $j = 1;
        for ($i = 1; $i <= $months; $i++) {
            if ($date->englishDayOfWeek == 'Sunday') {
                $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($this->dateFrom));
                $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($this->dateTo));
                $sheets[] = new WeeklySheet($j.' - '.$i, $dateFrom, $dateTo, $i - $j);
                $j = $i + 1;
            }

            if ($i == $months && $date->englishDayOfWeek != 'Sunday') {
                $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($this->dateFrom));
                $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($this->dateTo));
                $sheets[] = new WeeklySheet($j.' - '.$i, $dateFrom, $dateTo, $i - $j);
                $j = $i + 1;
            }

            $date->addDay(1);
        }

        return $sheets;
    }
}
