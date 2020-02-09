<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class CutOffAccount extends Model
{
    protected $connection = 'tenant';

    protected $table = 'cut_off_accounts';

    /**
     * Get the cut off that owns the cut off account.
     */
    public function cutOff()
    {
        return $this->belongsTo(CutOff::class, 'cut_off_id');
    }
}
