<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * TODO
     * 01. Chart Profit
     * 02. Chart Revenue
     * 03. Chart Sales counter
     * 04. Chart Purchases counter
     * 05. Chart Sales value
     * 06. Chart Purchase value
     *
     *
     * Validation of group_by query params accepted value is (daily, weekly, monthly, quarterly, yearly)
     *
     */

    public function chartSalesValue(Request $request)
    {
        $salesInvoices = SalesInvoice::active()
            ->joinForm()
            ->selectRaw('CAST(SUM(' . SalesInvoice::getTableName('amount') . ') AS UNSIGNED) AS total_amount')
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
            ->selectRaw('CAST(SUM(' . PurchaseInvoice::getTableName('amount') . ') AS UNSIGNED) AS total_amount')
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
}
