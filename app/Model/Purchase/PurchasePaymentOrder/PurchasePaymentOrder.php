<?php

namespace App\Model\Purchase\PurchasePaymentOrder;

use App\Model\Accounting\CutOff;
use App\Model\Form;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
use App\Model\TransactionModel;

class PurchasePaymentOrder extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'supplier_id',
        'due_date',
        'payment_type',
    ];

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function cutOffs()
    {
        return $this->hasMany(CutOff::class);
    }

    public function invoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function downPayments()
    {
        return $this->hasMany(PurchaseDownPayment::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function others()
    {
        return $this->hasMany(PurchasePaymentOrderOther::class);
    }

    public function create()
    {

    }
}
