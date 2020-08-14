<?php

namespace App\Model\Sales\SalesDownPayment;

use App\Contracts\Model\Transaction;
use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\SalesDownPaymentJoin;
use App\Traits\Model\Sales\SalesDownPaymentRelation;

class SalesDownPayment extends TransactionModel implements Transaction
{
    use SalesDownPaymentRelation, SalesDownPaymentJoin;

    public static $morphName = 'SalesDownPayment';

    protected $connection = 'tenant';

    public static $alias = 'sales_down_payment';

    public $timestamps = false;

    protected $fillable = [
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'DP';

    public function isAllowedToUpdate() {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function updateReference() {

    }

    public function updateStatus() {

    }

    private function isNotReferenced()
    {
        // Check if not referenced by sales order
        if ($this->invoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by sales invoice(s)', $this->invoices);
        }
        info($this->payments->count());
        if ($this->payments->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by payment(s)', $this->payments());
        }
    }

    public static function create($data)
    {
        $downPayment = new self;
        $reference = null;

        if (! empty($data['sales_order_id'])) {
            $downPayment->downpaymentable_id = $data['sales_order_id'];
            $downPayment->downpaymentable_type = SalesOrder::$morphName;

            $reference = SalesOrder::findOrFail($data['sales_order_id']);
        } elseif (! empty($data['sales_contract_id'])) {
            $downPayment->downpaymentable_id = $data['sales_contract_id'];
            $downPayment->downpaymentable_type = SalesContract::$morphName;

            $reference = SalesContract::findOrFail($data['sales_contract_id']);
        }

        $downPayment->fill($data);
        $downPayment->customer_id = $reference->customer_id;
        $downPayment->customer_name = $reference->customer_name;
        $downPayment->remaining = 0;
        $downPayment->save();

        $form = new Form;
        $form->saveData($data, $downPayment);

        return $downPayment;
    }
}
