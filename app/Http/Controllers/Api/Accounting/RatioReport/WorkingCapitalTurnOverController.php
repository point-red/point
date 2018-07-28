<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use App\Helpers\Ratio\WorkingCapitalTurnOver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkingCapitalTurnOverController extends Controller
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
        $ratio = new WorkingCapitalTurnOver();
        return $ratio->get($request->get('date_from'), $request->get('date_to'));
    }
}
