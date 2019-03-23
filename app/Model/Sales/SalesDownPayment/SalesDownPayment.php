<?php

namespace App\Model\Sales\SalesDownPayment;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class SalesDownPayment extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'DP';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    /**
     * Get all of the owning downpaymentable models.
     */
    public function downpaymentable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function create($data)
    {
        $downPayment = new self;
        $reference = null;

        if (!empty($data['sales_order_id'])) {
            $downPayment->downpaymentable_id = $data['sales_order_id'];
            $downPayment->downpaymentable_type = SalesOrder::class;

            $reference = SalesOrder::findOrFail($data['sales_order_id']);
        } else if (!empty($data['sales_contract_id'])) {
            $downPayment->downpaymentable_id = $data['sales_contract_id'];
            $downPayment->downpaymentable_type = SalesContract::class;

            $reference = SalesContract::findOrFail($data['sales_contract_id']);
        }

        $data['customer_id'] = $reference->customer_id;
        $data['customer_name'] = $reference->customer_name;
        $data['number'] = "DP/{code_customer}/{y}{m}{increment=4}";
        
        $downPayment->fill($data);
        $downPayment->save();

        $form = new Form;
        $form->fillData($data, $downPayment);

        return $downPayment;
    }
}
