<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Master\User;
use App\Model\MasterModel;

class SalesVisitationTarget extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitation_targets';

    protected $fillable = ['date', 'user_id', 'call', 'effective_call', 'value'];

    protected $casts = [
        'call' => 'double',
        'effective_call' => 'double',
        'value' => 'double',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function target($dateFrom, $dateTo)
    {
        $query = self::whereIn('date', function ($query) use ($dateTo) {
            $query->selectRaw('max(date)')->from(self::getTableName())->where('date', '<=', $dateTo)->groupBy('user_id');
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
}
