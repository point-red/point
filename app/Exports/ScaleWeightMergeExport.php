<?php
/**
 * Created by PhpStorm.
 * User: blegoh
 * Date: 14/01/19
 * Time: 8:29.
 */

namespace App\Exports;

use App\Model\Plugin\ScaleWeight\ScaleWeightTruck;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ScaleWeightMergeExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting
{
    private $key = [
        'Date In' => 'date_in',
        'Time In' => 'time_in',
        'Date Out' => 'date_out',
        'Time Out' => 'time_out',
        'Machine' => 'machine_code',
        'Form Number' => 'form_number',
        'Vendor' => 'vendor',
        'Driver' => 'driver',
        'License Number' => 'license_number',
        'Item' => 'item',
        'Gross' => 'gross_weight',
        'Tare' => 'tare_weight',
        'Net' => 'net_weight',
        'User' => 'user',
        'Date' => 'item_date',
        'Time' => 'item_time',
        'Item Machine' => 'item_machine_code',
        'Item Form Number' => 'item_form_number',
        'Item Vendor' => 'item_vendor',
        'Item Driver' => 'item_driver',
        'Box' => 'box',
        'Item Item' => 'item_item',
        'Item Gross' => 'item_gross_weight',
        'Item Tare' => 'item_tare_weight',
        'Item Net' => 'item_net_weight',
        'Item User' => 'item_user',
    ];

    public function __construct(string $dateFrom, string $dateTo, array $headers, array $cat = [])
    {
        $this->headers = $headers;
        $this->cat = $cat;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $from_sub_q = 'FROM '.config('database.connections.tenant.database').'.scale_weight_items 
                WHERE license_number = scale_weight_trucks.license_number AND time BETWEEN scale_weight_trucks.time_in AND scale_weight_trucks.time_out ) as';
        $merge = ScaleWeightTruck::whereRaw("time_in >= '$this->dateFrom'")
            ->whereRaw("time_in <= '$this->dateTo'")
            ->select('license_number', DB::raw("DATE_FORMAT(time_in, '%d/%m/%Y') as date_in"),
                DB::raw("DATE_FORMAT(time_out, '%d/%m/%Y') as date_out"),
                DB::raw("DATE_FORMAT(time_in, '%H.%i') as time_in"),
                DB::raw("DATE_FORMAT(time_out, '%H.%i') as time_out"),
                'gross_weight', 'net_weight', 'tare_weight', 'machine_code', 'vendor', 'driver', 'form_number',
                'item', 'user',
                DB::raw("(SELECT COUNT(*) $from_sub_q box"),
                DB::raw("(SELECT SUM(gross_weight) $from_sub_q item_gross_weight"),
                DB::raw("(SELECT SUM(net_weight) $from_sub_q item_net_weight"),
                DB::raw("(SELECT SUM(tare_weight) $from_sub_q item_tare_weight"),
                DB::raw("(SELECT MAX(vendor) $from_sub_q item_vendor"),
                DB::raw("(SELECT MAX(DATE_FORMAT(time, '%d/%m/%Y')) $from_sub_q item_date"),
                DB::raw("(SELECT MAX(DATE_FORMAT(time, '%H.%i')) $from_sub_q item_time"),
                DB::raw("(SELECT MAX(driver) $from_sub_q item_driver"),
                DB::raw("(SELECT MAX(user) $from_sub_q item_user"),
                DB::raw("(SELECT MAX(machine_code) $from_sub_q item_machine_code"),
                DB::raw("(SELECT MAX(form_number) $from_sub_q item_form_number"),
                DB::raw("(SELECT MAX(item) $from_sub_q item_item"),
                DB::raw("(SELECT MAX(user) $from_sub_q item_user")
            )
            ->orderBy('scale_weight_trucks.time_in');
        if (count($this->cat) > 0) {
            $merge->whereIn('scale_weight_trucks.item', $this->cat);
        } else {
            $merge->whereIn('scale_weight_trucks.item', ['~']);
        }

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
        $x = ['Time In', 'Time Out', 'Time'];
        foreach ($this->headers as $header) {
            if (in_array($header, $x)) {
                $a[] = $row->{$this->key[$header]}.' ';
            } else {
                $a[] = $row->{$this->key[$header]};
            }
        }

        return $a;
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        $a = ['Time In', 'Time Out', 'Time'];
        $format = [];
        $i = 0;
        foreach ($this->key as $key => $value) {
            if (in_array($key, $a)) {
                $format[chr($i + 65)] = NumberFormat::FORMAT_TEXT;
            }
            $i++;
        }

        return $format;
    }
}
