<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint\Report\Accumulation;

use Carbon\Carbon;
use App\Model\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;

class RepeatOrderReportController extends Controller
{
    public function index(Request $request)
    {
        $sales = [];
        $carbonDate = Carbon::parse(date('Y-m-01 00:00:00', strtotime($request->get('date'))));
        $months = $carbonDate->daysInMonth;
        $j = 1;
        for ($i = 1; $i <= $months; $i++) {
            if ($carbonDate->englishDayOfWeek == 'Sunday') {
                array_push($sales, $this->getSales($carbonDate, $j, $i));
                $j = $i + 1;
            }

            if ($i == $months && $carbonDate->englishDayOfWeek != 'Sunday') {
                array_push($sales, $this->getSales($carbonDate, $j, $i));
                $j = $i + 1;
            }

            $carbonDate->addDay(1);
        }

        return response()->json([
            'data' => [
                'sales' => $sales,
            ],
        ], 200);
    }

    private function getSales($date, $j, $i)
    {
        $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($date));
        $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($date));

        $sales = SalesVisitation::join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->join(Form::getTableName(), Form::getTableName().'.id', '=', SalesVisitation::getTableName().'.form_id')
            ->orderBy(SalesVisitation::getTableName().'.customer_id')
            ->groupBy(SalesVisitationDetail::getTableName().'.sales_visitation_id')
            ->select(SalesVisitation::getTableName().'.customer_id')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->get()
            ->toArray();

        $total = count($sales);
        $unique = count(array_unique(array_flatten($sales)));

        return [
            'repeat' => $total - $unique,
            'new' => $unique,
            'total' => $total,
        ];
    }
}
