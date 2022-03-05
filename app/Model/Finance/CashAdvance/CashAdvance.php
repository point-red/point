<?php

namespace App\Model\Finance\CashAdvance;

use App\Exceptions\IsReferencedException;
use App\Exceptions\PointException;
use App\Model\Form;
use App\Model\UserActivity;
use App\Model\TransactionModel;
use App\Traits\Model\Finance\CashAdvanceJoin;
use App\Traits\Model\Finance\CashAdvanceRelation;
use Carbon\Carbon;

class CashAdvance extends TransactionModel
{
    use CashAdvanceJoin, CashAdvanceRelation;

    public static $morphName = 'CashAdvance';

    protected $connection = 'tenant';

    public static $alias = 'cash_advance';

    public $timestamps = false;

    protected $fillable = [
        'payment_type',
        'employee_id',
    ];

    protected $casts = [
        'amount' => 'double',
        'amount_remaining' => 'double',
    ];

    public $defaultNumberPrefix = 'CA';


    public function setPaymentTypeAttribute($value)
    {
        $this->attributes['payment_type'] = strtoupper($value);
    }

    public function isAllowedToUpdate()
    {
        // Check if not referenced by another form
        if (optional($this->payments)->count()) {
            throw new IsReferencedException('Cannot edit form because it is already paid', $this->payments);
        }
    }

    public function isAllowedToDelete()
    {
        // Check if not referenced by another form
        if (optional($this->payments)->count()) {
            throw new IsReferencedException('Cannot delete form because it is already paid', $this->payments);
        }
    }

    public static function create($data)
    {
        $cashAdvance = new self;
        $cashAdvance->fill($data);

        $cashAdvanceDetails = self::mapCashAdvanceDetails($data['details'] ?? []);

        $amount = self::calculateAmount($cashAdvanceDetails);

        if ($amount < 0) {
            throw new PointException('You have negative amount');
        }

        $cashAdvance->amount = $amount;
        $cashAdvance->amount_remaining = $amount;
        $cashAdvance->save();

        $cashAdvance->details()->saveMany($cashAdvanceDetails);

        $form = new Form;
        $form->saveData($data, $cashAdvance);

        return $cashAdvance;
    }

    private static function calculateAmount($cashAdvanceDetails)
    {
        return array_reduce($cashAdvanceDetails, function ($carry, $detail) {
            return $carry + $detail['amount'];
        }, 0);
    }

    private static function mapCashAdvanceDetails($details)
    {
        return array_map(function ($detail) {
            $cashAdvanceDetail = new CashAdvanceDetail;
            $cashAdvanceDetail->fill($detail);

            return $cashAdvanceDetail;
        }, $details);
    }

    public static function mapHistory($cashAdvance, $data)
    {
        $history = new UserActivity;
        $history->fill($data);

        $history->table_type = self::$morphName;
        $history->table_id = $cashAdvance->id;
        $history->number = $cashAdvance->form->number;
        $history->user_id = optional(auth()->user())->id;
        $history->date = date('Y-m-d H:i:s');
        
        $history->save();
    }

    public function archive()
    {
        // Archiving
        $this->archived_at = date("Y-m-d H:i:s");
        $this->save();

        // Archiving form
        $this->form->archive();
    }
}
