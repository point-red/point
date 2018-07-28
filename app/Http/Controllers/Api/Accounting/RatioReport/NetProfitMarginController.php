<?php

namespace App\Http\Controllers\Api\Accounting\RatioReport;

use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NetProfitMarginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $currentAssets = ['sales income'];
        $currentLiability = ['cost of sales', 'direct expense'];

        $dateFrom = date('Y-m-d 00:00:00', strtotime($request->get('date_from')));
        $dateTo = date('Y-m-d 00:00:00', strtotime($request->get('date_to')));
        $date = date('Y-m-d 23:59:59', strtotime($dateFrom));
        $dateTimeFrom = new Datetime($dateFrom);
        $dateTimeTo = new DateTime($dateTo);
        $months = $dateTimeFrom->diff($dateTimeTo)->m + 1;

        $values = [];
        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            log_object('a'.$date);

            log_object('b'.$date);
            array_push($labels, date('M Y', strtotime($date)));

            $chartOfAccountIds = \App\Model\Accounting\ChartOfAccount::join('chart_of_account_types',
                'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
                ->whereIn('chart_of_account_types.name', $currentAssets)
                ->select('chart_of_accounts.*')
                ->pluck('id');
            $totalCurrentAsset = \App\Model\Accounting\Journal::whereIn('chart_of_account_id', $chartOfAccountIds)
                ->where('date', '<=', date('Y-m-t 23:59:59', strtotime($date)))
                ->selectRaw('sum(debit) - sum(credit) as total')
                ->pluck('total');

            $chartOfAccountIds = \App\Model\Accounting\ChartOfAccount::join('chart_of_account_types',
                'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
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

            $date = date('Y-m-d 23:59:59', strtotime($date.' +1 Months'));
        }

        return response()->json([
            'data' => [
                'description' => 'rasio untuk mengukur kemampuan perusahaan dalam mendapatkan laba bersih dari penjualan (semakin tinggi semakin baik)',
                'result' => '',
                'labels' => $labels,
                'values' => $values,
            ],
        ]);
    }
}
