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
        return $this->hasMany(get_class(new CutOffDetail()), 'cut_off_id');
    }
}
