<?php

namespace App\Model\Accounting;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Master\Expedition;
use App\Model\Master\Supplier;
use App\Model\PointModel;

class CutOffPayment extends PointModel
{
    protected $connection = 'tenant';

    public static $alias = 'cutoff_payment';

    protected $table = 'cutoff_payments';

    public static $morphName = 'CutOffPayment';

    protected $fillable = [
        'date',
        'amount',
        'notes',
    ];

    /**
     * Get all of the item's journals.
     */
    public function cutOffDetail()
    {
        return $this->morphOne(CutOffDetail::class, 'cutoffable');
    }

    public function cutoff_paymentable()
    {
        return $this->morphTo();
    }

    public static function getCutOffPaymentableType($subLedger)
    {
        $morphName = null;
        if ($subLedger === 'CUSTOMER') {
            $morphName = Customer::$morphName;
        } elseif ($subLedger === 'SUPPLIER') {
            $morphName = Supplier::$morphName;
        } elseif ($subLedger === 'EXPEDITION') {
            $morphName = Expedition::$morphName;
        } elseif ($subLedger === 'EMPLOYEE') {
            $morphName = Employee::$morphName;
        } 
        
        return $morphName;
    }
}
