<?php

namespace App\Exports\PinPoint\Performance;

use App\Model\Master\Item;
use App\Model\Master\User;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationTarget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class WeeklySheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    /**
     * Constructor.
     *
     * @param string $date
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct(string $date, string $dateFrom, string $dateTo)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
        $this->date = $date;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $array = [
            'Name',
            'Target Call',
            'Target Effective Call',
            'Target Value',
            'Actual Call',
            'Actual Effective Call',
            'Actual Value',
            'Actual Call (%)',
            'Actual Effective Call (%)',
            'Actual Value (%)',
        ];

        foreach (Item::all() as $item) {
            array_push($array, $item->name);
        }

        return $array;
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $array = [
            $row->name,
            $row->target_call,
            $row->target_effective_call,
            $row->target_value,
            $row->actual_call,
            $row->actual_effective_call,
            $row->actual_value,
            $row->target_call > 0 ? $row->actual_call / $row->target_call * 100 . '%' : 0 . '%',
            $row->target_effective_call > 0 ? $row->actual_effective_call / $row->target_effective_call * 100 . '%' : 0 . '%',
            $row->target_value > 0 ? $row->actual_value / $row->target_value * 100 . '%' : 0 . '%',
        ];

        foreach (Item::all() as $item) {
            foreach ($row->items as $itemSold) {
                if ($item->id == $itemSold->item_id) {
                    array_push($array, $itemSold->quantity);
                    break;
                } else {
                    array_push($array, 0);
                }
            }
        }


        return $array;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->date;
    }

    public function queryTarget($dateFrom)
    {
        $query = SalesVisitationTarget::whereIn('date', function ($query) use ($dateFrom) {
            $query->selectRaw('max(date)')->from(SalesVisitationTarget::getTableName())->where('date', '<=', $dateFrom)->groupBy('user_id');
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
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->addSelect('forms.created_by');
    }

    public function queryDetails($dateFrom, $dateTo)
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->leftJoin(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->rightJoin('items', 'items.id', '=', SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy(SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity) as quantity')
            ->addSelect('forms.created_by')
            ->addSelect('items.id as item_id')
            ->addSelect('items.name as name')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->orderBy('item_id')
            ->get();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $queryTarget = $this->queryTarget($this->dateFrom);
        $queryCall = $this->queryCall($this->dateFrom, $this->dateTo);
        $queryEffectiveCall = $this->queryEffectiveCall($this->dateFrom, $this->dateTo);
        $queryValue = $this->queryValue($this->dateFrom, $this->dateTo);
        $details = $this->queryDetails($this->dateFrom, $this->dateTo);

        $result = User::query()->leftJoinSub($queryTarget, 'queryTarget', function ($join) {
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
            ->addSelect('queryCall.total as actual_call')
            ->addSelect('queryEffectiveCall.total as actual_effective_call')
            ->addSelect('queryValue.value as actual_value')
            ->where('queryTarget.call', '>', 0)
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

        return $result;
    }
}
