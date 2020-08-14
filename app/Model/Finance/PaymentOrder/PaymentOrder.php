<?php

namespace App\Model\Finance\PaymentOrder;

use App\Exceptions\IsReferencedException;
use App\Exceptions\PointException;
use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\Finance\PaymentOrderJoin;
use App\Traits\Model\Finance\PaymentOrderRelation;
use Carbon\Carbon;

class PaymentOrder extends TransactionModel
{
    use PaymentOrderJoin, PaymentOrderRelation;

    public static $morphName = 'PaymentOrder';

    protected $connection = 'tenant';

    public static $alias = 'payment_order';

    public $timestamps = false;

    protected $fillable = [
        'payment_type',
        'due_date',
        'paymentable_type',
        'paymentable_id',
        'paymentable_name',
        'payment_account_id',
    ];

    protected $casts = [
        'amount' => 'double',
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

    public function setPaymentTypeAttribute($value)
    {
        $this->attributes['payment_type'] = strtoupper($value);
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by another form
        if (optional($this->payment)->count()) {
            throw new IsReferencedException('Cannot edit form because it is already paid', $this->payment);
        }
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by another form
        if (optional($this->payment)->count()) {
            throw new IsReferencedException('Cannot delete form because it is already paid', $this->payment);
        }
    }

    public static function create($data)
    {
        $paymentOrder = new self;
        $paymentOrder->fill($data);

        $paymentOrderDetails = self::mapPaymentOrderDetails($data['details'] ?? []);

        $paymentOrder->amount = self::calculateAmount($paymentOrderDetails);

        if ($paymentOrder->amount < 0) {
            throw new PointException('You have negative amount');
        }

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
