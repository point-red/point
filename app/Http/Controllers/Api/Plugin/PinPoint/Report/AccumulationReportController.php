<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint\Report;

use App\Http\Controllers\Controller;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationNoInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\Form;

class AccumulationReportController extends Controller
{
    protected $model;
    public function __construct(Request $request){
        switch ($request->filterId) {
            case 1:
                $this->model = new SalesVisitationInterestReason();
                break;
            case 2:
                $this->model = new SalesVisitationNoInterestReason();
                break;
            case 3:
                $this->model = new SalesVisitationSimilarProduct();
                break;
            case 4:
                $this->model = new SalesVisitationDetail();
                break;
        }
    }

    public function index(Request $request)
    {
        if($request->filterId == 4){ 
            return $this->getRepeatOrder($request->date,$request->branchId);
        };
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

        $result = $this->model::join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', $this->model::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->select($this->model::getTableName().'.name as name')
            ->where(SalesVisitation::getTableName().'.branch_id',$request->branchId);

        foreach ($queries as $key => $query) {
            $weekNum = $key + 1;
            $result = $result->leftJoinSub($query, 'week'.$weekNum, function ($join) use ($weekNum) {
                $join->on($this->model::getTableName().'.name', '=', 'week'.$weekNum.'.name');
            })->addSelect(DB::raw('coalesce(week'.$weekNum.'.count, 0) as week'.$weekNum));
        }

        $result = $result->addSelect(DB::raw('count(*) as monthly'))
            ->whereBetween('forms.date', [date_from($request->get('date'), true), date_to($request->get('date'), true)])
            ->orderBy('monthly', 'desc')
            ->groupBy($this->model::getTableName().'.name')
            ->get();

        return response()->json([
            'data' => [
                'sales'=>[],
                'reasons' => $result,
                'totalPerWeek' => $totalPerWeek,
            ],
        ], 200);
    }

    private function getRepeatOrder($date,$branchId){
        $sales = [];
        $carbonDate = Carbon::parse(date('Y-m-01 00:00:00', strtotime($date)));
        $months = $carbonDate->daysInMonth;
        $j = 1;
        for ($i = 1; $i <= $months; $i++) {
            if ($carbonDate->englishDayOfWeek == 'Sunday') {
                array_push($sales, $this->getSales($carbonDate, $j, $i,$branchId));
                $j = $i + 1;
            }

            if ($i == $months && $carbonDate->englishDayOfWeek != 'Sunday') {
                array_push($sales, $this->getSales($carbonDate, $j, $i,$branchId));
                $j = $i + 1;
            }

            $carbonDate->addDay(1);
        }

        return response()->json([
            'data' => [
                'sales' => $sales,
                'reasons' => [],
                'totalPerWeek' => [],
            ],
        ], 200);
    }

    private function getSales($date, $j, $i, $branchId)
    {
        $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($date));
        $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($date));

        $sales = SalesVisitation::join($this->model::getTableName(), $this->model::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->join(Form::getTableName(), Form::getTableName().'.id', '=', SalesVisitation::getTableName().'.form_id')
            ->orderBy(SalesVisitation::getTableName().'.customer_id')
            ->groupBy($this->model::getTableName().'.sales_visitation_id')
            ->select(SalesVisitation::getTableName().'.customer_id')
            ->where(SalesVisitation::getTableName().'.branch_id',$branchId)
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

    private function getInterestReason($date, $j, $i)
    {
        $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($date));
        $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($date));

        $reasons = $this->model::join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', $this->model::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->select($this->model::getTableName().'.name as name')
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

        $totalCount = $this->model::join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', $this->model::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->select(DB::raw('count(*) as count'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->orderBy('count', 'desc')
            ->first();

        return $totalCount->count;
    }
}
