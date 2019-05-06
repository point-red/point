<?php

namespace App\Model\Purchase\PurchaseDownPayment;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;
use App\Exceptions\IsReferencedException;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseContract\PurchaseContract;

class PurchaseDownPayment extends TransactionModel
{
    public static $morphName = 'PurchaseDownPayment';

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'supplier_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'PDP';

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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(PurchaseInvoice::class, 'purchase_down_payment_invoice', 'invoice_id', 'down_payment_id')->active();
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by purchase invoice
        if ($this->invoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by invoice', $this->invoices);
        }
    }

    public static function create($data)
    {
        $downPayment = new self;
        $reference = null;

        if (! empty($data['purchase_order_id'])) {
            $downPayment->downpaymentable_id = $data['purchase_order_id'];
            $downPayment->downpaymentable_type = PurchaseOrder::class;

            $reference = PurchaseOrder::findOrFail($data['purchase_order_id']);
        } elseif (! empty($data['purchase_contract_id'])) {
            $downPayment->downpaymentable_id = $data['purchase_contract_id'];
            $downPayment->downpaymentable_type = PurchaseContract::class;

            $reference = findOrFail($data['purchase_contract_id']);
        }

        $downPayment->supplier_id = $reference->supplier_id;
        $downPayment->supplier_name = $reference->supplier_name;
        $downPayment->fill($data);
        $downPayment->remaining = $data['amount'];
        $downPayment->save();

        $form = new Form;
        $form->saveData($data, $downPayment);

        return $downPayment;
    }
}
