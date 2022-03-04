<?php

namespace App\Model\Accounting;

use App\Model\PointModel;
use App\Traits\Model\General\FormableOne;
use App\Traits\Model\General\GeneralJoin;

class CutOffDetail extends PointModel
{
    use GeneralJoin, FormableOne;

    protected $connection = 'tenant';

    public static $alias = 'cut_off_account';

    protected $table = 'cutoff_details';

    protected $fillable = [
        'cutoff_id',
    ];

    /**
     * Get all of the owning journalable models.
     */
    public function cutoffable()
    {
        return $this->morphTo();
    }
}
