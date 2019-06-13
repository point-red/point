<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\Accounting\Journal;
use App\Http\Controllers\Controller;
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

    public function statTotalReceivable()
    {
        $receivables = Journal::join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'account receivable')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other account receivable');
            })
            ->selectRaw('SUM(`credit`) AS credit, SUM(`debit`) AS debit')
            ->first();

        return $receivables->debit - $receivables->credit;
    }

    public function statTotalPayable()
    {
        $payables = Journal::join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'current liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'long term liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other current liability');
            })
            ->selectRaw('SUM(`credit`) AS credit, SUM(`debit`) AS debit')
            ->first();

        return $payables->credit - $payables->debit;
    }
}
