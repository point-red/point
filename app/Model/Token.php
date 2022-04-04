<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Master\User;

class Token extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'tokens';

    public static $alias = 'token';

    public $timestamps = true;

    protected $fillable = [
        'user_id', 'token'
    ];

    /**
     * Get all of the owning table models.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}