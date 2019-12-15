<?php

namespace App\Model\Accounting;

use App\Model\TransactionModel;

class CutOff extends TransactionModel
{
    public static $morphName = 'CutOff';

    protected $connection = 'tenant';

    protected $table = 'cut_offs';

    /**
     * Get the details for the cut off.
     */
    public function details()
    {
        return $this->hasMany(CutOffDetail::class, 'cut_off_id');
    }
}
