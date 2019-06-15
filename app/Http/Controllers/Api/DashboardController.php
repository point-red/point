<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\Accounting\Journal;
use App\Http\Controllers\Controller;
use App\Model\Finance\Payment\Payment;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;

class DashboardController extends Controller
{
    public function chartSalesValue(Request $request)
    {
        $salesInvoices = SalesInvoice::active()
            ->joinForm()
            ->selectRaw('CAST(SUM(' . SalesInvoice::getTableName('amount') . ') AS UNSIGNED) AS value')
            ->periodic($request->get('period'))
            ->get();

        return $salesInvoices;
    }

    public function chartSalesCount(Request $request)
    {
        $salesInvoices = SalesInvoice::active()
            ->joinForm()
            ->selectRaw('COUNT(' . SalesInvoice::getTableName('id') . ') AS count')
            ->periodic($request->get('period'))
            ->get();

        return $salesInvoices;
    }

    public function chartPurchaseValue(Request $request)
    {
        $purchaseInvoices = PurchaseInvoice::active()
            ->joinForm()
            ->selectRaw('CAST(SUM(' . PurchaseInvoice::getTableName('amount') . ') AS UNSIGNED) AS value')
            ->periodic($request->get('period'))
            ->get();

        return $purchaseInvoices;
    }

    public function chartPurchaseCount(Request $request)
    {
        $purchaseInvoices = PurchaseInvoice::active()
            ->joinForm()
            ->selectRaw('COUNT(' . PurchaseInvoice::getTableName('id') . ') AS count')
            ->periodic($request->get('period'))
            ->get();

        return $purchaseInvoices;
    }

    public function chartPaymentReceived(Request $request)
    {
        $paymentReceived = Payment::activeDone()
            ->joinForm()
            ->where('disbursed', false)
            ->selectRaw('SUM(`amount`) AS amount')
            ->periodic($request->get('period'))
            ->get();

        return $paymentReceived;
    }

    public function chartPaymentSent(Request $request)
    {
        $paymentSent = Payment::activeDone()
            ->joinForm()
            ->where('disbursed', true)
            ->selectRaw('SUM(`amount`) AS amount')
            ->periodic($request->get('period'))
            ->get();

    return $paymentSent;
    }
}
