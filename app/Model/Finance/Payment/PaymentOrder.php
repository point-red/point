<?php

namespace App\Model\Finance\Payment;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use App\Model\HumanResource\Employee\Employee;

class PaymentOrder extends Payment
{
    protected $connection = 'tenant';

    protected $table = 'payments';

    public $timestamps = false;

    protected $fillable = [
        'payment_type',
        'payment_type_replacement',
        'disbursed',
        'due_date',
        'paymentable_type',
        'paymentable_id',
        'paymentable_name',
        'payment_account_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    protected $paymentableType = [
        'customer' => Customer::class,
        'supplier' => Supplier::class,
        'employee' => Employee::class,
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

    public function isAllowedToUpdate()
    {
        // TODO isAllowed to update?
    }

    public function isAllowedToDelete()
    {
        // TODO isAllowed to update?
    }

    public static function create($data)
    {
        $payment = new Payment;
        $payment->fill($data);
        
        $paymentDetails = self::mapPaymentDetails($data['details'] ?? []);
        
        $payment->amount = self::calculateAmount($paymentDetails);
        $payment->paymentable_name = $payment->paymentable->name;
        $payment->save();

        $payment->details()->saveMany($paymentDetails);

        $form = new Form;
        $form->fill($data);

        $form->formable_id = $payment->id;
        $form->formable_type = Payment::class;

        $form->generateFormNumber(
            self::generateFormNumber($payment, $data['number'] ?? null, $data['increment_group']),
            $data['paymentable_id'],
            $data['paymentable_id']
        );

        if (empty($data['approver_id'])) {
            $form->done = true;
        }

        $form->save();

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

    private static function generateFormNumber($payment, $number, $incrementGroup)
    {
        $defaultFormat = '{payment_type}{y}{m}{increment=4}';
        $formNumber = $number ?? $defaultFormat;

        // Different method to get increment because payment number is considering payment_type
        preg_match_all('/{increment=(\d)}/', $formNumber, $regexResult);
        if (! empty($regexResult)) {
            $lastPayment = Self::whereHas('form', function ($query) use ($incrementGroup) {
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

            foreach ($regexResult[0] as $key => $value) {
                $padUntil = $regexResult[1][$key];
                $result = str_pad($increment, $padUntil, '0', STR_PAD_LEFT);
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
}
