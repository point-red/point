<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Accounting\ChartOfAccount;
use Illuminate\Http\Request;

class PaymentAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $accounts = ChartOfAccount::from('chart_of_accounts as ' . ChartOfAccount::$alias)->eloquentFilter($request);

        $accounts = ChartOfAccount::joins($accounts, $request->get('join'));

        $accounts = pagination($accounts, $request->get('limit'));

        return new ApiCollection($accounts);
    }

}
