<?php

namespace App\Http\Controllers\Api\PaymentGateway\Xendit;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Account\Wallet;
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
            $data->xendit_id = $request->get('id');
            $data->fill($request->all());
            $data->save();

            $userId = explode('-', $data->external_id);

            $wallet = new Wallet;
            $wallet->user_id = $userId[1];
            $wallet->source_id = $data->id;
            $wallet->source_type = get_class($data);
            $wallet->amount = $data->amount;
            $wallet->save();

            return new ApiResource($data);
        }
    }

    public function fvaCreated(Request $request)
    {
        log_object($request->all());
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function fvaPaid(Request $request)
    {
        log_object($request->all());
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function retailOutletPaid(Request $request)
    {
        log_object($request->all());
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function cardRefunded(Request $request)
    {
        log_object($request->all());
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function disbursementSent(Request $request)
    {
        log_object($request->all());
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

    public function batchDisbursementSent(Request $request)
    {
        log_object($request->all());
        $verifyToken = $request->header('x-callback-token');
        if ($verifyToken != env('XENDIT_CALLBACK_VERIFICATION_TOKEN')) {
            return response()->json([], 400);
        } else {
            return response()->json([], 200);
        }
    }

}
