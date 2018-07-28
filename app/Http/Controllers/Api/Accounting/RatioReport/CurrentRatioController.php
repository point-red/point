<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use App\Helpers\Ratio\CurrentRatio;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrentRatioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $ratio = new CurrentRatio();
        return $ratio->get($request->get('date_from'), $request->get('date_to'));
    }
}
