<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcidTestRatioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentAssets = ['cash', 'bank', 'cheque', 'inventory', 'account receivable', 'other account receivable'];
        $otherAssets = ['fixed asset', 'other asset'];
        $cashEquivalent = ['cash', 'bank', 'cheque'];
        $accountReceivable = ['account receivable', 'other account receivable'];
        $currentLiability = ['current liability', 'other current liability'];

        $dateFrom = date('Y-m-d 00:00:00', strtotime($request->get('date_from')));
        $dateTo = date('Y-m-d 00:00:00', strtotime($request->get('date_to')));
        $date = date('Y-m-d 23:59:59', strtotime($dateFrom));
        $dateTimeFrom = new Datetime($dateFrom);
        $dateTimeTo = new DateTime($dateTo);
        $months = $dateTimeFrom->diff($dateTimeTo)->m + 1;

        $values = [];
        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            array_push($labels, date('M Y', strtotime($date)));

            $chartOfAccountIds = \App\Model\Accounting\ChartOfAccount::join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
                ->whereIn('chart_of_account_types.name', array_merge($cashEquivalent, $accountReceivable))
                ->select('chart_of_accounts.*')
                ->pluck('id');
            $totalCurrentAsset = \App\Model\Accounting\Journal::whereIn('chart_of_account_id', $chartOfAccountIds)
                ->where('date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
                ->selectRaw('sum(debit) - sum(credit) as total')
                ->pluck('total');

            $chartOfAccountIds = \App\Model\Accounting\ChartOfAccount::join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
                ->whereIn('chart_of_account_types.name', $currentLiability)
                ->select('chart_of_accounts.*')
                ->pluck('id');
            $totalCurrentLiability = \App\Model\Accounting\Journal::whereIn('chart_of_account_id', $chartOfAccountIds)
                ->where('date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
                ->selectRaw('sum(credit) - sum(debit) as total')
                ->pluck('total');

            if ($totalCurrentAsset[0] == 0 || $totalCurrentLiability[0] == 0) {
                array_push($values, 0);
            } else {
                $value = $totalCurrentAsset[0] / $totalCurrentLiability[0];
                array_push($values, $value);
            }

            $date = date('Y-m-d 23:59:59', strtotime($date . ' +1 Months'));
        }

        return response()->json([
            'data' => [
                'description' => 'berapa kekuatan perusahaan untuk membayar hutang jangka pendek menggunakan aset yang siap diuangkan?',
                'result' => 'rasio untuk mengukur kemampuan perusahaan dalam membayar kewajiban finansial jangka pendek dengan mengunakan asset lancar yang lebih likuid (Liquid Assets), nilai ideal adalah 150%		',
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
