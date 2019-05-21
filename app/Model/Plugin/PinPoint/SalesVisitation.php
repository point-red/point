<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Form;
use App\Model\PointModel;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;

class SalesVisitation extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitations';

    protected $casts = [
        'total' => 'double',
        'value' => 'double',
    ];

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = convert_to_server_timezone($value);
    }

    public function getDueDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function interestReasons()
    {
        return $this->hasMany(SalesVisitationInterestReason::class);
    }

    public function notInterestReasons()
    {
        return $this->hasMany(SalesVisitationNotInterestReason::class);
    }

    public function similarProducts()
    {
        return $this->hasMany(SalesVisitationSimilarProduct::class);
    }

    public function details()
    {
        return $this->hasMany(SalesVisitationDetail::class);
    }

    public static function call($dateFrom, $dateTo, $userId)
    {
        $query = self::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->select(DB::raw('count(forms.id) as total'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('forms.created_by', $userId)
            ->groupBy('forms.created_by')
            ->first();

        return $query ? $query->total : 0;
    }

    public static function effectiveCall($dateFrom, $dateTo, $userId)
    {
        $querySalesVisitationHasDetail = self::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('pin_point_sales_visitation_details', 'pin_point_sales_visitation_details.sales_visitation_id', '=', 'pin_point_sales_visitations.id')
            ->select('pin_point_sales_visitations.id')
            ->addSelect(DB::raw('sum(pin_point_sales_visitation_details.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('pin_point_sales_visitations.id');

        $query = self::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->joinSub($querySalesVisitationHasDetail, 'query_sales_visitation_has_detail', function ($join) {
                $join->on('pin_point_sales_visitations.id', '=', 'query_sales_visitation_has_detail.id');
            })
            ->selectRaw('count(pin_point_sales_visitations.id) as total')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('forms.created_by', $userId)
            ->groupBy('forms.created_by')
            ->first();

        return $query ? $query->total : 0;
    }

    public static function value($dateFrom, $dateTo, $userId)
    {
        $query = self::join('forms', 'forms.id', '=', self::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', self::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->where('forms.created_by', $userId)
            ->first();

        return $query ? $query->value : 0;
    }
}
