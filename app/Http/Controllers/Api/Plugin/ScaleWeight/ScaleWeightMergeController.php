<?php

namespace App\Http\Controllers\Api\Plugin\ScaleWeight;

use App\Http\Controllers\Controller;
use App\Model\Plugin\ScaleWeight\ScaleWeightTruck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScaleWeightMergeController extends Controller
{
    public function index(Request $request)
    {
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');

        $from_sub_q = 'FROM '.config('database.connections.tenant.database').'.scale_weight_items 
                WHERE license_number = scale_weight_trucks.license_number AND time BETWEEN scale_weight_trucks.time_in AND scale_weight_trucks.time_out ) as';
        $merge = ScaleWeightTruck::whereRaw("time_in >= '$date_from'")
            ->whereRaw("time_in <= '$date_to'")
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

        if ($request->has('cat')) {
            $merge->whereIn('scale_weight_trucks.item', $request->cat);
        } else {
            $merge->whereIn('scale_weight_trucks.item', ['~']);
        }

        return response()->json(['data' => $merge->get()]);
    }

    public function item()
    {
        $items = ScaleWeightTruck::select('item')->distinct()->get()->pluck('item');

        return response()->json(['data' => $items]);
    }
}
