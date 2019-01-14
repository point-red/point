<?php
/**
 * Created by PhpStorm.
 * User: blegoh
 * Date: 14/01/19
 * Time: 8:29
 */

namespace App\Exports;


use App\Model\Plugin\ScaleWeight\ScaleWeightItem;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ScaleWeightMergeExport implements FromQuery, WithHeadings, WithMapping
{

    private $key = [
        "Date" => "date",
        "License Number" => "license_number",
        "Item Gross" => "item_gross_weight",
        "Item Tare" => "item_tare_weight",
        "Item Net" => "item_net_weight",
        "Truck Gross" => "truck_gross_weight",
        "Truck Tare" => "truck_tare_weight",
        "Truck Net" => "truck_net_weight",
    ];

    public function __construct(string $dateFrom, string $dateTo, Array $headers)
    {
        $this->headers = $headers;
        $this->dateFrom = date('Y-m-d', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d', strtotime($dateTo));
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $merge = DB::table(DB::raw('(SELECT license_number, DATE_FORMAT(time,"%Y-%m-%d") as date, SUM(gross_weight) as gross_weight,
                                    SUM(tare_weight) as tare_weight, SUM(net_weight) as net_weight FROM ' . config('database.connections.tenant.database') . '.scale_weight_items 
                                    WHERE DATE_FORMAT(time,"%Y-%m-%d") >= "' . $this->dateFrom . '" AND DATE_FORMAT(time,"%Y-%m-%d") <= "' . $this->dateTo . '" 
                                    GROUP BY license_number,DATE_FORMAT(time,"%Y-%m-%d") ) as i'))
            ->join(DB::raw('(SELECT license_number, DATE_FORMAT(time_in,"%Y-%m-%d") as date, SUM(gross_weight) as gross_weight,
                                    SUM(tare_weight) as tare_weight, SUM(net_weight) as net_weight FROM ' . config('database.connections.tenant.database') . '.scale_weight_trucks 
                                    WHERE DATE_FORMAT(time_in,"%Y-%m-%d") >= "' . $this->dateFrom . '" AND DATE_FORMAT(time_in,"%Y-%m-%d") <= "' . $this->dateTo . '" 
                                    GROUP BY license_number,DATE_FORMAT(time_in,"%Y-%m-%d") ) as t'), 'i.license_number', '=', 't.license_number')
            ->select('i.license_number', 'i.date', 'i.gross_weight as item_gross_weight', 'i.tare_weight as item_tare_weight', 'i.net_weight as item_net_weight',
                't.gross_weight as truck_gross_weight', 't.tare_weight as truck_tare_weight', 't.net_weight as truck_net_weight')
            ->orderBy('i.license_number');
        return $merge;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $a = [];
        foreach ($this->headers as $header){
            $a[] = $row->{$this->key[$header]};
        }
        return $a;
    }
}