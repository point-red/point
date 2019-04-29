<?php

namespace App\Model\Finance\PaymentOrder;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use Carbon\Carbon;
use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\TransactionModel;

class PaymentOrder extends TransactionModel
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
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

    public $defaultNumberPrefix = 'PAYORDER';

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    /**
     * Get all of the owning paymentable models.
     */
    public function paymentable()
    {
        return $this->morphTo();
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function details()
    {
        return $this->hasMany(PaymentOrderDetail::class);
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
        $paymentOrder = new self;
        $paymentOrder->fill($data);

        $paymentOrderDetails = self::mapPaymentOrderDetails($data['details'] ?? []);

        $paymentOrder->amount = self::calculateAmount($paymentOrderDetails);
        $paymentOrder->paymentable_name = $paymentOrder->paymentable->name;
        $paymentOrder->save();

        $paymentOrder->details()->saveMany($paymentOrderDetails);

        $form = new Form;
        $form->saveData($data, $paymentOrder);

        return $paymentOrder;
    }

    private static function calculateAmount($paymentOrderDetails)
    {
        return array_reduce($paymentOrderDetails, function ($carry, $detail) {
            return $carry + $detail['amount'];
        }, 0);
    }

    private static function mapPaymentOrderDetails($details)
    {
        return array_map(function ($detail) {
            $paymentOrderDetail = new PaymentOrderDetail;
            $paymentOrderDetail->fill($detail);

            return $paymentOrderDetail;
        }, $details);
    }
}
