<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint\Report\Accumulation;

use App\Http\Controllers\Controller;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimilarProductReportController extends Controller
{
    public function index(Request $request)
    {
        $queries = [];
        $totalPerWeek = [];
        $carbonDate = Carbon::parse(date('Y-m-01 00:00:00', strtotime($request->get('date'))));
        $months = $carbonDate->daysInMonth;
        $j = 1;
        for ($i = 1; $i <= $months; $i++) {
            if ($carbonDate->englishDayOfWeek == 'Sunday') {
                array_push($queries, $this->getInterestReason($carbonDate, $j, $i));
                array_push($totalPerWeek, $this->getTotalPerWeek($carbonDate, $j, $i));
                $j = $i + 1;
            }

            if ($i == $months && $carbonDate->englishDayOfWeek != 'Sunday') {
                array_push($queries, $this->getInterestReason($carbonDate, $j, $i));
                array_push($totalPerWeek, $this->getTotalPerWeek($carbonDate, $j, $i));
                $j = $i + 1;
            }

            $carbonDate->addDay(1);
        }

        $result = SalesVisitationSimilarProduct::join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', SalesVisitationSimilarProduct::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->select(SalesVisitationSimilarProduct::getTableName().'.name as name');

        foreach ($queries as $key => $query) {
            $weekNum = $key + 1;
            $result = $result->leftJoinSub($query, 'week'.$weekNum, function ($join) use ($weekNum) {
                $join->on(SalesVisitationSimilarProduct::getTableName().'.name', '=', 'week'.$weekNum.'.name');
            })->addSelect(DB::raw('coalesce(week'.$weekNum.'.count, 0) as week'.$weekNum));
        }

        $result = $result->addSelect(DB::raw('count(*) as monthly'))
            ->whereBetween('forms.date', [date_from($request->get('date'), true), date_to($request->get('date'), true)])
            ->orderBy('monthly', 'desc')
            ->groupBy(SalesVisitationSimilarProduct::getTableName().'.name')
            ->get();

        return response()->json([
            'data' => [
                'products' => $result,
                'totalPerWeek' => $totalPerWeek,
            ],
        ], 200);
    }

    private function getInterestReason($date, $j, $i)
    {
        $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($date));
        $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($date));

        $reasons = SalesVisitationSimilarProduct::join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', SalesVisitationSimilarProduct::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->select(SalesVisitationSimilarProduct::getTableName().'.name as name')
            ->addSelect(DB::raw('count(*) as count'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('name')
            ->orderBy('count', 'desc');

        return $reasons;
    }

    private function getTotalPerWeek($date, $j, $i)
    {
        $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($date));
        $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($date));

        $totalCount = SalesVisitationSimilarProduct::join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', SalesVisitationSimilarProduct::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->select(DB::raw('count(*) as count'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->orderBy('count', 'desc')
            ->first();

        return $totalCount->count;
    }
}
