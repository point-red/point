<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Account\Invoice;
use App\Model\Account\Wallet;
use App\Model\Plugin;
use Illuminate\Http\Request;
use Xendit\Xendit;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::where('user_id', '=', auth()->user()->id)->get();

        return new ApiResource($wallets);
    }

    public function amount()
    {
        $amount = Wallet::where('user_id', '=', auth()->user()->id)->sum('amount');

        return response()->json([
            'data' => [
                'amount' => (double) $amount
            ]
        ], 200);
    }

    public function topUp(Request $request)
    {
        Xendit::setApiKey(env('XENDIT_SECRET_API_KEY'));

        $invoices = \Xendit\Invoice::retrieveAll();
        $count = 0;
        $createInvoice = null;
        foreach ($invoices as $invoice) {
            if (strtolower($invoice['status']) == 'pending'
                && $invoice['amount'] == $request->get('amount')
                && date('Y-m-d H:i:s', strtotime($invoice['expiry_date'])) > date('Y-m-d H:i:s')) {
                // there is still pending invoice, avoid create new one
                $count++;
                $createInvoice = $invoice;
                break;
            }
        }

        if ($count == 0) {
            // create new invoice
            if ($request->get('invoice_id')) {
                $invoice = Invoice::find($request->get('invoice_id'));
                $params = [
                    'external_id' => 'invoice-' . $request->get('invoice_id'),
                    'payer_email' => auth()->user()->email,
                    'description' => 'INVOICE #'.$invoice->number,
                    'amount' => $request->get('amount')
                ];
            } else {
                $params = [
                    'external_id' => 'user-' . auth()->user()->id,
                    'payer_email' => auth()->user()->email,
                    'description' => 'Top-up',
                    'amount' => $request->get('amount')
                ];
            }

            $createInvoice = \Xendit\Invoice::create($params);

            return response()->json(['data' => ['invoice_url' => $createInvoice['invoice_url']]], 201);
        } else {
            // return invoice url
            return response()->json(['data' => ['invoice_url' => $createInvoice['invoice_url']]], 201);
        }
    }
}
