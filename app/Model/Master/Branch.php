<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Branch extends MasterModel
{
    protected $connection = 'tenant';

    protected $table = 'branches';

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id')->withPivot(['is_default']);
    }
}
