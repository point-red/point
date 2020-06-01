<?php

namespace App\Model\Purchase\PurchaseDownPayment;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Purchase\PurchaseContract\PurchaseContract;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\TransactionModel;
use App\Traits\Model\Purchase\PurchaseDownPaymentJoin;
use App\Traits\Model\Purchase\PurchaseDownPaymentRelation;

class PurchaseDownPayment extends TransactionModel
{
    use PurchaseDownPaymentRelation, PurchaseDownPaymentJoin;

    public static $morphName = 'PurchaseDownPayment';

    protected $connection = 'tenant';

    public static $alias = 'purchase_down_payment';

    public $timestamps = false;

    protected $fillable = [
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'PDP';

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
        if ($this->invoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by purchase invoice(s)', $this->invoices);
        }
    }

    public static function create($data)
    {
        $downPayment = new self;
        $reference = null;

        if (! empty($data['purchase_order_id'])) {
            $downPayment->downpaymentable_id = $data['purchase_order_id'];
            $downPayment->downpaymentable_type = PurchaseOrder::$morphName;

            $reference = PurchaseOrder::findOrFail($data['purchase_order_id']);
        } elseif (! empty($data['purchase_contract_id'])) {
            $downPayment->downpaymentable_id = $data['purchase_contract_id'];
            $downPayment->downpaymentable_type = PurchaseContract::$morphName;

            $reference = findOrFail($data['purchase_contract_id']);
        }

        $downPayment->fill($data);
        $downPayment->supplier_id = $reference->supplier_id;
        $downPayment->supplier_name = $reference->supplier_name;
        $downPayment->remaining = $data['amount'];
        $downPayment->save();

        $form = new Form;
        $form->saveData($data, $downPayment);

        return $downPayment;
    }
}
