<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Ratio\TotalDebtToEquityRatio;

class TotalDebtToEquityRatioController extends Controller
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
        $ratio = new TotalDebtToEquityRatio();

        return $ratio->get($request->get('date_from'), $request->get('date_to'));
    }
}
