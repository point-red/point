<?php

namespace App\Model\Sales\PaymentCollection;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\TransactionModel;

class PaymentCollectionDetail extends TransactionModel
{

    protected $connection = 'tenant';

    public static $alias = 'sales_payment_collection_detail';

    protected $table = 'sales_payment_collection_details';

    public $timestamps = false;

    protected $fillable = [
        'chart_of_account_id',
        'allocation_id',
        'available',
        'amount',
        'notes',
        'referenceable_form_date',
        'referenceable_form_number',
        'referenceable_form_notes',
        'referenceable_id',
        'referenceable_type'
    ];

    protected $casts = [
        'available' => 'double',
        'amount' => 'double'
    ];

    /**
     * Get all of the owning referenceable models.
     */

    public function referenceable()
    {
        return $this->morphTo();
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }


}