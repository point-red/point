<?php

namespace App\Traits\Model\Master;

use App\Model\Master\User;

trait BranchRelation
{
    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user')->withPivot(['is_default']);
    }
}
