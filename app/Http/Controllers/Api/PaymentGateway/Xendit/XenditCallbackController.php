<?php

namespace App\Http\Controllers\Api\PaymentGateway\Xendit;

use App\Http\Controllers\Controller;
use App\Model\PaymentGateway\Xendit\XenditInvoicePaid;
use Illuminate\Http\Request;

class XenditCallbackController extends Controller
{

    public function invoicePaid(Request $request)
    {
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            $data = new XenditInvoicePaid;

            return response()->json([], 200);
        }
    }

    public function fvaCreated(Request $request)
    {
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function fvaPaid(Request $request)
    {
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function retailOutletPaid(Request $request)
    {
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function cardRefunded(Request $request)
    {
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function disbursementSent(Request $request)
    {
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function batchDisbursementSent(Request $request)
    {
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

}
