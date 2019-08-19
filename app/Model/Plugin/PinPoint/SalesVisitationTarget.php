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

    public static function target($dateTo, $userId)
    {
        $query = self::whereIn('date', function ($query) use ($dateTo, $userId) {
            $query->selectRaw('max(date)')->from(self::getTableName())->where('date', '<=', $dateTo);
        })->where('user_id', $userId)->first();

        return [
            'call' => $query ? $query->call : 0,
            'effective_call' => $query ? $query->effective_call : 0,
            'value' => $query ? $query->value : 0,
        ];
    }
}
