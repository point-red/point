<?php

namespace App\Model\Finance\Payment;

use App\Model\Form;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Accounting\ChartOfAccount;

class Payment extends TransactionModel
{
    public static $morphName = 'Payment';

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'disbursed',
        'paymentable_type',
        'paymentable_id',
        'paymentable_name',
        'payment_account_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function paymentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'payment_account_id');
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

    public function isAllowedToUpdate()
    {
        // TODO isAllowed to update?
    }

    public function isAllowedToDelete()
    {
        // TODO isAllowed to delete?
    }

    public static function create($data)
    {
        $payment = new self;
        $payment->fill($data);
        $payment->payment_type = strtoupper($payment->paymentAccount->type->name);
        $payment->paymentable_name = $data['paymentable_name'] ?? $payment->paymentable->name;

        $paymentDetails = self::mapPaymentDetails($data['details']);

        $payment->amount = self::calculateAmount($paymentDetails);
        $payment->save();

        $payment->details()->saveMany($paymentDetails);

        $form = new Form;
        $form->fill($data);
        $form->done = true;
        $form->approved = true;

        $form->formable_id = $payment->id;
        $form->formable_type = self::$morphName;
        $form->increment = self::getLastPaymentIncrement($payment, $data['increment_group']);
        $form->generateFormNumber(
            self::generateFormNumber($payment, $data['number'] ?? null, $form->increment),
            $data['paymentable_id'],
            $data['paymentable_id']
        );
        $form->save();

        self::updateReferenceDone($paymentDetails);
        self::updateJournal($payment);

        return $payment;
    }

    private static function mapPaymentDetails($details)
    {
        return array_map(function ($detail) {
            $paymentDetail = new PaymentDetail;
            $paymentDetail->fill($detail);

            return $paymentDetail;
        }, $details);
    }

    private static function calculateAmount($paymentDetails)
    {
        return array_reduce($paymentDetails, function ($carry, $detail) {
            return $carry + $detail['amount'];
        }, 0);
    }

    private static function generateFormNumber($payment, $number, $increment)
    {
        $defaultFormat = '{payment_type}-{disbursed}{y}{m}{increment=4}';
        $formNumber = $number ?? $defaultFormat;

        preg_match_all('/{increment=(\d)}/', $formNumber, $regexResult);
        foreach ($regexResult[0] as $key => $value) {
            $padUntil = $regexResult[1][$key];
            $result = str_pad($increment, $padUntil, '0', STR_PAD_LEFT);
            $formNumber = str_replace($value, $result, $formNumber);
        }

        // Additional template for payment_type and disbursed
        if (strpos($formNumber, '{payment_type}') !== false) {
            $formNumber = str_replace('{payment_type}', $payment->payment_type, $formNumber);
        }
        if (strpos($formNumber, '{disbursed}') !== false) {
            $replacement = $payment->disbursed ? 'OUT' : 'IN';
            $formNumber = str_replace('{disbursed}', $replacement, $formNumber);
        }

        return $formNumber;
    }

    /**
     * Different method to get increment
     * because payment number is
     * considering payment_type and disbursed.
     */
    private static function getLastPaymentIncrement($payment, $incrementGroup)
    {
        $lastPayment = self::whereHas('form', function ($query) use ($incrementGroup) {
            $query->where('increment_group', $incrementGroup);
        })
        ->notArchived()
        ->where('payment_type', $payment->payment_type)
        ->where('disbursed', $payment->disbursed)
        ->with('form')
        ->get()
        ->sortByDesc('form.increment')
        ->first();

        $increment = 1;
        if (! empty($lastPayment)) {
            $increment += $lastPayment->form->increment;
        }

        return $increment;
    }

    private static function updateReferenceDone($paymentDetails)
    {
        foreach ($paymentDetails as $paymentDetail) {
            if (! $paymentDetail->isDownPayment()) {
                $reference = $paymentDetail->referenceable;
                $reference->remaining -= $paymentDetail->amount;
                $reference->save();
                $reference->updateIfDone();
            }
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
            $journal->form_id_reference = optional(optional($paymentDetail->referenceable)->form)->id;
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
