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
        'disbursed',
        'due_date',
        'paymentable_type',
        'paymentable_id',
    ];

    protected $casts = [
        'amount' => 'double',
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

    public function setPaymentTypeAttribute($value)
    {
        $this->attributes['payment_type'] = strtoupper($value);
    }

    public static function create($data)
    {
        $data['payment_type'] = strtoupper($data['payment_type']);
        $payment = new self;
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
                    ->joinForm()
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
        $form->fill($data);
        $form->formable_id = $payment->id;
        $form->formable_type = Payment::class;

        $defaultFormat = '{payment_type}-{disbursed}{y}{m}{increment=4}';
        $formNumber = $data['number'] ?? $defaultFormat;

        // Different method to get increment because payment number is considering payment_type
        preg_match_all('/{increment=(\d)}/', $formNumber, $regexResult);
        if (!empty($regexResult)) {
            $increment = Payment::joinForm()
                ->notArchived()
                ->whereMonth(Form::getTableName('date'), date('n', strtotime($data['date'])))
                ->where(Payment::getTableName('payment_type'), $payment->payment_type)
                ->where(Payment::getTableName('disbursed'), $payment->disbursed)
                ->count();

            foreach ($regexResult[0] as $key => $value) {
                $padUntil = $regexResult[1][$key];
                $result = str_pad($increment + 1, $padUntil, '0', STR_PAD_LEFT);
                $formNumber = str_replace($value, $result, $formNumber);
            }
        }

        // Additional template for payment_type and disbursed
        if (strpos($formNumber, '{payment_type}') !== false) {
            $formNumber = str_replace('{payment_type}', $payment->payment_type, $formNumber);
        }
        if (strpos($formNumber, '{disbursed}') !== false) {
            $replacement = $data['disbursed'] == false ? 'IN' : 'OUT';
            $formNumber = str_replace('{disbursed}', $replacement, $formNumber);
        }

        $form->generateFormNumber(
            $formNumber,
            $data['paymentable_id'],
            $data['paymentable_id']
        );
        $form->save();

        return $payment;
    }
}
