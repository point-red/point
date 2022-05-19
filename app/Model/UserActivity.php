<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Master\User;

class UserActivity extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'user_activities';

    public static $alias = 'user_activity';

    public $timestamps = false;

    protected $fillable = [
        'table_type', 'table_id', 'number',
        'date', 'user_id', 'activity',
    ];

    /**
     * Get all of the owning table models.
     */
    public function table()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
