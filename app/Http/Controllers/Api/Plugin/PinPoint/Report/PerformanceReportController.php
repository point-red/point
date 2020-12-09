<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\Plugin\PinPoint\Report\Performance\PerformanceCollection;
use App\Model\HumanResource\Kpi\Automated;
use App\Model\Master\Branch;
use App\Model\Master\User;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = convert_to_server_timezone(date('Y-m-d H:i:s', strtotime($request->get('date_from'))));
        $dateTo = convert_to_server_timezone(date('Y-m-d H:i:s', strtotime($request->get('date_to'))));
        $numberOfDays = Automated::getDays($request->get('date_from'), $request->get('date_to'));

        $branchId = $request->get('branch_id');
        $branch = Branch::find($branchId);

        $queryTarget = $this->queryTarget($dateFrom, $dateTo);
        $queryCall = $this->queryCall($dateFrom, $dateTo);
        $queryEffectiveCall = $this->queryEffectiveCall($dateFrom, $dateTo);
        $queryValue = $this->queryValue($dateFrom, $dateTo);
        $details = $this->queryDetails($dateFrom, $dateTo);

        $result = User::leftJoinSub($queryTarget, 'queryTarget', function ($join) {
            $join->on('users.id', '=', 'queryTarget.user_id');
        })->leftJoinSub($queryCall, 'queryCall', function ($join) {
            $join->on('users.id', '=', 'queryCall.created_by');
        })->leftJoinSub($queryEffectiveCall, 'queryEffectiveCall', function ($join) {
            $join->on('users.id', '=', 'queryEffectiveCall.created_by');
        })->leftJoinSub($queryValue, 'queryValue', function ($join) {
            $join->on('users.id', '=', 'queryValue.created_by');
        })->select('users.id')
            ->addSelect('users.name')
            ->addSelect('users.first_name')
            ->addSelect('users.last_name')
            ->addSelect('queryTarget.call as target_call')
            ->addSelect('queryTarget.effective_call as target_effective_call')
            ->addSelect('queryTarget.value as target_value')
            ->addSelect('queryCall.total as call')
            ->addSelect('queryEffectiveCall.total as effective_call')
            ->addSelect('queryValue.value as value')
            ->where('queryTarget.call', '>', 0);

        if ($branch) {
            $branchUsers = $branch->users;
            $ids = $branchUsers->pluck('id')->toArray();
            $result = $result->whereIn('users.id', $ids);
        }

        $result = $result->groupBy('users.id')
            ->get();

        $totals = [
            'target_call' => 0,
            'target_effective_call' => 0,
            'target_value' => 0,
            'call' => 0,
            'effective_call' => 0,
            'value' => 0,
            'items' => [],
        ];

        foreach ($result as $user) {
            $values = array_values($details->filter(function ($value) use ($user) {
                return $value->created_by == $user->id;
            })->all());

            foreach ($values as $value) {
                unset($value->created_by);
            }

            $user->items = $values;

            $user->target_call = $user->target_call * $numberOfDays;
            $user->target_effective_call = $user->target_effective_call * $numberOfDays;
            $user->target_value = $user->target_value * $numberOfDays;

            $totals['target_call'] += $user->target_call;
            $totals['target_effective_call'] += $user->target_effective_call;
            $totals['target_value'] += $user->target_value;
            $totals['call'] += $user->call;
            $totals['effective_call'] += $user->effective_call;
            $totals['value'] += $user->value;

            foreach ($user->items as $item) {
                if (array_key_exists($item->item_id, $totals['items'])) {
                    $totals['items'][$item->item_id]['quantity'] += $item->quantity;
                } else {
                    $totals['items'][$item->item_id]['item_id'] = $item->item_id;
                    $totals['items'][$item->item_id]['quantity'] = $item->quantity;
                }
            }
        }

        $totals['items'] = array_values($totals['items']);

        return (new PerformanceCollection($result))
            ->additional([
                'data_set' => [
                    'totals' => $totals,
                ],
            ]);
    }

    public function queryTarget($dateFrom, $dateTo)
    {
        $query = SalesVisitationTarget::whereIn('date', function ($query) use ($dateTo) {
            $query->selectRaw('max(date)')->from(SalesVisitationTarget::getTableName())->where('date', '<=', $dateTo)->groupBy('user_id');
        });

        $targets = User::leftJoinSub($query, 'query', function ($join) {
            $join->on('users.id', '=', 'query.user_id');
        })->select('query.id as id')
            ->addSelect('users.name as name')
            ->addSelect('users.id as user_id')
            ->addSelect('query.date as date')
            ->addSelect('query.call as call')
            ->addSelect('query.effective_call as effective_call')
            ->addSelect('query.value as value')
            ->groupBy('users.id');

        return $targets;
    }

    public function queryCall($dateFrom, $dateTo)
    {
        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->select('forms.created_by as created_by')
            ->addselect(DB::raw('count(forms.id) as total'))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('forms.created_by');
    }

    public function queryEffectiveCall($dateFrom, $dateTo)
    {
        $querySalesVisitationHasDetail = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('pin_point_sales_visitation_details', 'pin_point_sales_visitation_details.sales_visitation_id', '=', 'pin_point_sales_visitations.id')
            ->select('pin_point_sales_visitations.id')
            ->addSelect(DB::raw('sum(pin_point_sales_visitation_details.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('pin_point_sales_visitations.id');

        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->joinSub($querySalesVisitationHasDetail, 'query_sales_visitation_has_detail', function ($join) {
                $join->on('pin_point_sales_visitations.id', '=', 'query_sales_visitation_has_detail.id');
            })->selectRaw('count(pin_point_sales_visitations.id) as total')
            ->addSelect('forms.created_by')
            ->addSelect(DB::raw('query_sales_visitation_has_detail.totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('forms.created_by');
    }

    public function queryValue($dateFrom, $dateTo)
    {
        return SalesVisitation::join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->where(function ($q) {
                $q->where(SalesVisitation::getTableName('payment_method'), '=', 'cash')->orWhere(SalesVisitation::getTableName('payment_method'), '=', 'credit');
            })
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->addSelect('forms.created_by');
    }

    public function queryDetails($dateFrom, $dateTo)
    {
        return SalesVisitation::join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->leftJoin(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->rightJoin('items', 'items.id', '=', SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy(SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity) as quantity')
            ->addSelect('forms.created_by')
            ->addSelect('items.id as item_id')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->orderBy('item_id')
            ->get();
    }
}
