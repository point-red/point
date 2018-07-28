<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use App\Helpers\Ratio\AcidTestRatio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcidTestRatioController extends Controller
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
        $acidTestRatio = new AcidTestRatio();
        return $acidTestRatio->get($request->get('date_from'), $request->get('date_to'));
    }
}
