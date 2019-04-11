<?php

namespace App\Model\Finance\Payment;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;

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
        'payment_account_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    protected $paymentableType = [
        'customer' => Customer::class,
        'supplier' => Supplier::class,
    ];

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

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
        $payment = new self;
        $payment->fill($data);

        $paymentDetails = self::getPaymentDetails($data['details'] ?? []);

        $payment->amount = self::getAmounts($paymentDetails);
        $payment->paymentable_name = $payment->paymentable->name;
        $payment->save();

        $payment->details()->saveMany($paymentDetails);

        $form = new Form;
        $form->fill($data);

        $form->formable_id = $payment->id;
        $form->formable_type = self::class;

        $form->generateFormNumber(
            self::getPaymentFormNumber($payment, $data['number'], $data['increment_group']),
            $data['paymentable_id'],
            $data['paymentable_id']
        );
        $form->save();

        self::updateReferenceDone($paymentDetails);
        self::updateJournal($payment);

        return $payment;
    }

    private static function getPaymentDetails($details)
    {
        $paymentDetails = [];

        foreach ($details as $detail) {
            $paymentDetail = new PaymentDetail;
            $paymentDetail->fill($detail);

            array_push($paymentDetails, $paymentDetail);
        }

        return $paymentDetails;
    }

    private static function getAmounts($paymentDetails)
    {
        return array_reduce($paymentDetails, function ($carry, $detail) {
            return $carry + $detail['amount'];
        }, 0);
    }

    private static function getPaymentFormNumber($payment, $number, $incrementGroup)
    {
        $defaultFormat = '{payment_type}-{disbursed}{y}{m}{increment=4}';
        $formNumber = $number ?? $defaultFormat;

        // Different method to get increment because payment number is considering payment_type
        preg_match_all('/{increment=(\d)}/', $formNumber, $regexResult);
        if (! empty($regexResult)) {
            $lastPayment = Self::whereHas('form', function($query) use($incrementGroup){
                $query->whereNotNull('number')
                    ->where('increment_group', $incrementGroup);
            })
            ->where('payment_type', $payment->payment_type)
            ->where('disbursed', $payment->disbursed)
            ->with(['form' => function($query) {
                $query->orderBy('increment', 'desc');
            }])
            ->first();
    
            $increment = 1;
    
            if (! empty($lastPayment)) {
                $increment += $lastPayment->form->increment;
            }

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
            $replacement = $payment->disbursed === false ? 'IN' : 'OUT';
            $formNumber = str_replace('{disbursed}', $replacement, $formNumber);
        }

        return $formNumber;
    }

    private static function updateReferenceDone($paymentDetails)
    {
        foreach ($paymentDetails as $paymentDetail) {
            $reference = $paymentDetail->referenceable;
            $reference->remaining -= $paymentDetail->amount;
            $reference->updateIfDone();
            $reference->save();
        }
    }

    private static function updateJournal($payment)
    {
        $journal = new Journal;
        $journal->form_id = $payment->form->id;
        $journal->journalable_type = $payment->paymentable_type;
        $journal->journalable_id = $payment->paymentable_id;
        $journal->chart_of_account_id = $payment->payment_account_id;
        if (! $payment->disbursed) {
            $journal->debit = $payment->amount;
        } else {
            $journal->credit = $payment->amount;
        }
        $journal->save();

        foreach ($payment->details as $paymentDetail) {
            $journal = new Journal;
            $journal->form_id = $payment->form->id;
            $journal->form_id_reference = $paymentDetail->referenceable->form->id;
            $journal->journalable_type = $paymentDetail->referenceable_type;
            $journal->journalable_id = $paymentDetail->referenceable_id;
            $journal->chart_of_account_id = $paymentDetail->chart_of_account_id;
            if (! $payment->disbursed) {
                $journal->credit = $paymentDetail->amount;
            } else {
                $journal->debit = $paymentDetail->amount;
            }
            $journal->save();
        }
    }
}
