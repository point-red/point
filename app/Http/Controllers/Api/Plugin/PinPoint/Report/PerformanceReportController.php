<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint\Report;

use App\Http\Resources\Plugin\PinPoint\Report\Performance\PerformanceCollection;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PerformanceReportController extends Controller
{
    public function index()
    {
        $queryCall = $this->queryCall();
        $queryEffectiveCall = $this->queryEffectiveCall();
        $queryValue = $this->queryValue();
        $details = $this->queryDetails();

        $result = \App\Model\Master\User::joinSub($queryCall, 'queryCall', function ($join) {
            $join->on('users.id', '=', 'queryCall.created_by');
        })->joinSub($queryEffectiveCall, 'queryEffectiveCall', function ($join) {
            $join->on('users.id', '=', 'queryEffectiveCall.created_by');
        })->joinSub($queryValue, 'queryValue', function ($join) {
            $join->on('users.id', '=', 'queryValue.created_by');
        })->select('users.id')
            ->addSelect('users.name')
            ->addSelect('users.first_name')
            ->addSelect('users.last_name')
            ->addSelect('queryCall.total as call')
            ->addSelect('queryEffectiveCall.total as effective_call')
            ->addSelect('queryValue.value as value')
            ->groupBy('users.id')
            ->get();

        foreach ($result as $user) {
            $values = array_values($details->filter(function ($value) use ($user) {
                return $value->created_by == $user->id;
            })->all());

            foreach ($values as $value) {
                unset($value->created_by);
            }

            $user->items = $values;
        }

        return new PerformanceCollection($result);
    }

    public function queryCall()
    {
        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->select('forms.created_by as created_by')
            ->addselect(DB::raw('count(forms.id) as total'))
            ->groupBy('forms.created_by');
    }

    public function queryEffectiveCall()
    {
        $querySalesVisitationHasDetail = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('pin_point_sales_visitation_details', 'pin_point_sales_visitation_details.sales_visitation_id', '=', 'pin_point_sales_visitations.id')
            ->select('pin_point_sales_visitations.id')
            ->addSelect(DB::raw('sum(pin_point_sales_visitation_details.quantity) as totalQty'))
            ->groupBy('pin_point_sales_visitations.id');

        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->joinSub($querySalesVisitationHasDetail, 'query_sales_visitation_has_detail', function ($join) {
                $join->on('pin_point_sales_visitations.id', '=', 'query_sales_visitation_has_detail.id');
            })->selectRaw('count(pin_point_sales_visitations.id) as total')
            ->addSelect('forms.created_by')
            ->addSelect(DB::raw('query_sales_visitation_has_detail.totalQty'))
            ->groupBy('forms.created_by');
    }

    public function queryValue()
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->addSelect('forms.created_by');
    }

    public function queryDetails()
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->leftJoin(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->rightJoin('items', 'items.id', '=', SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy(SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity) as quantity')
            ->addSelect('forms.created_by')
            ->addSelect('items.id as item_id')
            ->orderBy('item_id')
            ->get();
    }
}
