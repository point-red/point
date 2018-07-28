<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use App\Helpers\Ratio\CashRatio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CashRatioController extends Controller
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
        $cashRatio = new CashRatio();
        return $cashRatio->get($request->get('date_from'), $request->get('date_to'));
    }
}
