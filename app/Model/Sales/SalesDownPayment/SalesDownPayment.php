<?php

namespace App\Model\Sales\SalesDownPayment;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\TransactionModel;
use App\Model\Finance\Payment\Payment;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesContract\SalesContract;

class SalesDownPayment extends TransactionModel
{
    public static $morphName = 'SalesDownPayment';

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
        'remaining' => 'double',
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

    public function invoices()
    {
        return $this->belongsToMany(SalesInvoice::class, 'sales_down_payment_invoice', 'invoice_id', 'down_payment_id')
            ->withPivot('amount');
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
        if ($this->invoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by sales invoice(s)', $this->invoices);
        }
    }

    public function updateIfDone()
    {
        $used = $this->invoices->sum(function ($invoice) {
            return $invoice->pivot->amount;
        });
        $done = $this->amount - $used <= 0;
        $this->form()->update(['done' => $done]);
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
        $downPayment->remaining = $downPayment->amount;
        $downPayment->save();

        $form = new Form;
        $form->saveData($data, $downPayment);

        // Add Payment
        if (! empty($data['payment_account_id'])) {
            self::addPayment($data, $downPayment);
        }

        return $downPayment;
    }

    private static function addPayment($data, $downPayment)
    {
        $payment = [
            // payment type should be cash / bank when paid = true
            'payment_account_id' => $data['payment_account_id'],
            'date' => $data['date'],
            'number' => $data['payment_number'] ?? null,
            'disbursed' => false,
            'paymentable_id' => $downPayment->customer_id,
            'paymentable_type' => Customer::$morphName,
            'paymentable_name' => $downPayment->customer_name,
            'increment_group' => $data['increment_group'],

            'details' => [
                [
                    'chart_of_account_id' => get_setting_journal('sales', 'down payment'),
                    'allocation_id' => $data['allocation_id'] ?? null,
                    'amount' => $downPayment->amount,
                    'notes' => $data['notes'] ?? null,
                    'referenceable_type' => self::$morphName,
                    'referenceable_id' => $downPayment->id,
                ],
            ],
        ];

        $payment = Payment::create($payment);

        $downPayment->paid_by = $payment->id;
        $downPayment->save();
    }
}
