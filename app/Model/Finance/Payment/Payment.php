<?php

namespace App\Model\Finance\Payment;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;

class Payment extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'payment_type',
        'due_date',
        'disbursed',
        'paymentable_type',
        'paymentable_id',
    ];

    protected $cast = [
        'amount' => 'double',
    ];

    protected $paymentType = [
        'cash',
        'bank',
    ];

    protected $paymentableType = [
        'customer' => Customer::class,
        'supplier' => Supplier::class,
    ];

    public function details()
    {
        return $this->hasMany(PaymentDetail::class, 'payment_id');
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    /**
     * Get all of the owning paymentable models.
     */
    public function paymentable()
    {
        return $this->morphTo();
    }

    public function setPaymentableTypeAttribute($value)
    {
        $this->attributes['paymentable_type'] = $this->paymentableType[$value];
    }

    public static function create($data)
    {
        if ($data['disbursed'] === true) {
            if ($data['payment_type'] === 'cash') {
                $payment = new PaymentCashIn;
            }
            else if ($data['payment_type'] === 'bank') {
                $payment = new PaymentBankIn;
            }
        }
        else {
            if ($data['payment_type'] === 'cash') {
                $payment = new PaymentCashOut;
            }
            else if ($data['payment_type'] === 'bank') {
                $payment = new PaymentBankOut;
            }
        }
        $payment->fill($data);

        $paymentAmount = 0;
        $paymentDetails = [];

        // TODO validation details is required and must be array
        $details = $data['details'] ?? [];
        if (!empty($details) && is_array($details)) {
            foreach ($details as $detail) {
                $paymentDetail = new PaymentDetail;
                $paymentDetail->fill($detail);

                $reference = $paymentDetail->referenceable_type::findOrFail($paymentDetail->referenceable_id);
                
                $paidAmountInThePast = Payment::where('referenceable_id', $paymentDetail->referenceable_id)
                    ->where('referenceable_type', $paymentDetail->referenceable_type)
                    ->join(Form::getTableName(), Payment::getTableName('id'), '=', Form::getTableName('formable_id'))
                    ->where(function ($query) {
                        $query->where(Form::getTableName('formable_type'), PaymentBankOut::class)
                            ->orWhere(Form::getTableName('formable_type'), PaymentCashOut::class);
                    })
                    ->active()
                    ->join(PaymentDetail::getTableName(), Payment::getTableName('id'), '=', PaymentDetail::getTableName('payment_id'))
                    ->select(PaymentDetail::getTableName('amount'))
                    ->get()
                    ->sum('amount');

                // Prevent overpaid
                if ($reference->amount < $paidAmountInThePast + $detail['amount']) {
                    // TODO throw error because overpaid
                }

                if ($reference->amount == $paidAmountInThePast + $detail['amount']) {
                    $reference->form()->update(['done' => true]);
                }

                $paymentAmount += $detail['amount'];

                array_push($paymentDetails, $paymentDetail);
            }
        }

        if (!empty($data['done']) && $data['done'] === true) {
            // TODO increase / decrease cash
        }

        $payment->amount = $paymentAmount;
        $payment->paymentable_name = $payment->paymentable->name;
        $payment->save();

        $payment->details()->saveMany($paymentDetails);

        $form = new Form;
        $form->fillData($data, $payment);

        return $payment;
    }
}
