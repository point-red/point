<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use App\Helpers\Ratio\GrossProfitRatio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GrossProfitRatioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $grossProfitRatio = new GrossProfitRatio();
        return $grossProfitRatio->get($request->get('date_from'), $request->get('date_to'));
    }
}
