<?php

namespace App\Http\Controllers\Api\PaymentGateway\Xendit;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Account\Invoice;
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
            $isExists = XenditInvoicePaid::where('xendit_id', $request->get('id'))->first();

            if (! $isExists) {
                $data = new XenditInvoicePaid;
                $data->xendit_id = $request->get('id');
                $data->fill($request->all());
                $data->save();

                $externalId = explode('-', $data->external_id);

                if ($externalId[0] == 'user') {
                    $wallet = new Wallet;
                    $wallet->user_id = $externalId[1];
                    $wallet->source_id = $data->id;
                    $wallet->source_type = get_class($data);
                    $wallet->amount = $data->amount;
                    $wallet->save();
                }

                if ($externalId[0] == 'invoice') {
                    $invoice = Invoice::find($externalId[1]);
                    $invoice->paidable_type = XenditInvoicePaid::class;
                    $invoice->paidable_id = $data->id;
                    $invoice->save();
                }

                if ($invoice->project->is_generated == false) {
                    $invoice->project->generate();
                }

                return new ApiResource($data);
            } else {
                return response()->json([]);
            }
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
