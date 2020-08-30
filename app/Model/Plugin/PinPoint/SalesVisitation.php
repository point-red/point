<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Master\Warehouse;
use App\Model\PointModel;
use App\Traits\Model\Plugin\PinPoint\SalesVisitationJoin;
use App\Traits\Model\Plugin\PinPoint\SalesVisitationRelation;
use Illuminate\Support\Facades\DB;

class SalesVisitation extends PointModel
{
    use SalesVisitationRelation, SalesVisitationJoin;

    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitations';

    protected $casts = [
        'total' => 'double',
        'value' => 'double',
    ];

    public static $alias = 'sales_visitation';

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = convert_to_server_timezone($value);
    }

    public function getDueDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public static function call($dateFrom, $dateTo)
    {
        return self::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->select('forms.created_by as created_by')
            ->addselect(DB::raw('count(forms.id) as total'))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('forms.created_by');
    }

    public static function effectiveCall($dateFrom, $dateTo)
    {
        $querySalesVisitationHasDetail = self::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('pin_point_sales_visitation_details', 'pin_point_sales_visitation_details.sales_visitation_id', '=', 'pin_point_sales_visitations.id')
            ->select('pin_point_sales_visitations.id')
            ->addSelect(DB::raw('sum(pin_point_sales_visitation_details.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('pin_point_sales_visitations.id');

        return self::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->joinSub($querySalesVisitationHasDetail, 'query_sales_visitation_has_detail', function ($join) {
                $join->on('pin_point_sales_visitations.id', '=', 'query_sales_visitation_has_detail.id');
            })->selectRaw('count(pin_point_sales_visitations.id) as total')
            ->addSelect('forms.created_by')
            ->addSelect(DB::raw('query_sales_visitation_has_detail.totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('forms.created_by');
    }

    public static function value($dateFrom, $dateTo)
    {
        return self::join('forms', 'forms.id', '=', self::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', self::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->addSelect('forms.created_by');
    }

    public static function detail($dateFrom, $dateTo)
    {
        return self::join('forms', 'forms.id', '=', self::getTableName().'.form_id')
            ->leftJoin(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', self::getTableName().'.id')
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
