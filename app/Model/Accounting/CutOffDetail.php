<?php

namespace App\Model\Accounting;

use Illuminate\Database\Eloquent\Model;

class CutOffDetail extends Model
{
    protected $connection = 'tenant';

    protected $table = 'cut_off_details';

    protected $casts = [
        'debit' => 'double',
        'credit' => 'double',
    ];

    /**
     * Get the chart of account that owns the cut off detail.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(get_class(new ChartOfAccount()), 'chart_of_account_id');
    }
}
