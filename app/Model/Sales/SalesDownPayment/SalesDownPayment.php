<?php

namespace App\Model\Sales\SalesDownPayment;

use App\Model\Finance\Payment\Payment;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesInvoice\SalesInvoice;
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

    public function invoices()
    {
        return $this->belongsToMany(SalesInvoice::class, 'sales_down_payment_invoice', 'invoice_id', 'down_payment_id')
            ->withPivot('amount');
    }

    public function updateIfDone()
    {
        $used = $this->invoices->sum(function($invoice) {
            return $invoice->pivot->amount;
        });
        if ($this->amount - $used <= 0) {
            $this->form()->update(['done' => true]);
        }
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
        if (empty($data['number'])) {
            $data['number'] = "DP/{code_customer}/{y}{m}{increment=4}";
        }
        
        $downPayment->fill($data);
        $downPayment->save();

        $form = new Form;
        $form->fillData($data, $downPayment);

        // Add Payment Collection
        self::addPaymentCollection($data, $downPayment);

        return $downPayment;
    }

    private static function addPaymentCollection($data, $downPayment) {
        $payment = [];
        // payment type should be cash / bank when paid = true
        $payment['payment_type'] = $data['payment_type'] ?? 'payment collection';
        $payment['payment_account_id'] = $data['payment_account_id'];
        $payment['due_date'] = $data['due_date'] ?? null;
        $payment['date'] = $downPayment->form->date;
        $payment['number'] = $data['payment_number'] ?? null;
        $payment['done'] = $data['payment_done'] ?? false;
        $payment['approved'] = $data['payment_approved'] ?? false;
        $payment['disbursed'] = false;
        $payment['amount'] = $downPayment->amount;
        $payment['paymentable_id'] = $downPayment->customer_id;
        $payment['paymentable_type'] = 'customer';
        $payment['paymentable_name'] = $downPayment->customer->name;

        $payment['details'] = [
            0 => [
                'chart_of_account_id' => 1,
                'allocation_id' => null,
                'amount' => $downPayment->amount,
                'notes' => $downPayment->form->notes,
                'referenceable_type' => 'sales_down_payment',
                'referenceable_id' => $downPayment->id,
            ]
        ];

        $salePayment = Payment::create($payment);
        
        $downPayment->paid_by = $salePayment->id;
        $downPayment->save();
    }
}
