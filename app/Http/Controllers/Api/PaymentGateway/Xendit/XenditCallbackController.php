<?php

namespace App\Http\Controllers\Api\PaymentGateway\Xendit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class XenditCallbackController extends Controller
{
    public function invoicePaid(Request $request)
    {
        log_object($request->all());
    }

    public function fvaCreated(Request $request)
    {
        log_object($request->all());
    }

    public function fvaPaid(Request $request)
    {
        log_object($request->all());
    }

    public function retailOutletPaid(Request $request)
    {
        log_object($request->all());
    }

    public function cardRefunded(Request $request)
    {
        log_object($request->all());
    }

    public function disbursementSent(Request $request)
    {
        log_object($request->all());
    }

    public function batchDisbursementSent(Request $request)
    {
        log_object($request->all());
    }

}
