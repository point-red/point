<?php

namespace App\Http\Controllers\Api\Plugin\ScaleWeight;

use App\Model\Plugin\ScaleWeight\ScaleWeightItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ScaleWeightMergeController extends Controller
{
    public function index(Request $request)
    {
        $date_from = date('Y-m-d', strtotime($request->get('date_from')));
        $date_to = date('Y-m-d', strtotime($request->get('date_to')));

        $merge = DB::table(DB::raw('(SELECT license_number, DATE_FORMAT(time,"%Y-%m-%d") as date, SUM(gross_weight) as gross_weight,
                                    SUM(tare_weight) as tare_weight, SUM(net_weight) as net_weight FROM '.config('database.connections.tenant.database').'.scale_weight_items 
                                    WHERE DATE_FORMAT(time,"%Y-%m-%d") >= "'.$date_from.'" AND DATE_FORMAT(time,"%Y-%m-%d") <= "'.$date_to.'" 
                                    GROUP BY license_number,DATE_FORMAT(time,"%Y-%m-%d") ) as i'))
                ->join(DB::raw('(SELECT license_number, DATE_FORMAT(time_in,"%Y-%m-%d") as date, SUM(gross_weight) as gross_weight,
                                    SUM(tare_weight) as tare_weight, SUM(net_weight) as net_weight FROM '.config('database.connections.tenant.database').'.scale_weight_trucks 
                                    WHERE DATE_FORMAT(time_in,"%Y-%m-%d") >= "'.$date_from.'" AND DATE_FORMAT(time_in,"%Y-%m-%d") <= "'.$date_to.'" 
                                    GROUP BY license_number,DATE_FORMAT(time_in,"%Y-%m-%d") ) as t'),'i.license_number','=','t.license_number')
            ->select('i.license_number','i.date','i.gross_weight as item_gross_weight','i.tare_weight as item_tare_weight','i.net_weight as item_net_weight',
                                't.gross_weight as truck_gross_weight','t.tare_weight as truck_tare_weight','t.net_weight as truck_net_weight')
            ->orderBy('i.license_number')
            ->get();
        return response()->json(['data' => $merge]);
    }
}
