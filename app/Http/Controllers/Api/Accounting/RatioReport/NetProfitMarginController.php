<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use App\Helpers\Ratio\NetProfitMargin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NetProfitMarginController extends Controller
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
        $ratio = new NetProfitMargin();
        return $ratio->get($request->get('date_from'), $request->get('date_to'));
    }
}
