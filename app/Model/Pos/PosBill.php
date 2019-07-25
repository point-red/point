<?php

namespace App\Model\Pos;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\TransactionModel;

class PosBill extends TransactionModel
{
    public static $morphName = 'PosBill';

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'invoice_number',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
        'amount',
        'paid',
        'notes',
    ];

    protected $casts = [
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
        'amount' => 'double',
        'paid' => 'double',
        'remaining' => 'double',
    ];

    public $defaultNumberPrefix = 'BI';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(PosBillItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // public function bill

    public function updateIfDone()
    {
        $done = $this->remaining <= 0;
        $this->form()->update(['done' => $done]);
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
    }

    public static function create($data)
    {
        $bill = new self;
        $bill->fill($data);

        $items = self::mapItems($data['items'] ?? []);

        $bill->amount = self::calculateAmount($bill, $items);
        $bill->paid = $bill->paid;
        $bill->remaining = $bill->paid >= $bill->amount ? 0 : $bill->amount - $bill->paid;

        $bill->save();

        $bill->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $bill);

        // updated to done if the remaining is 0
        $bill->updateIfDone();

        return $bill;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $billItem = new PosBillItem;
            $billItem->fill($item);

            return $billItem;
        }, $items);
    }

    private static function calculateAmount($bill, $items)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * $item->converter * ($item->price - $item->discount_value);
        }, 0);

        $amount -= $bill->discount_value;
        $amount += $bill->type_of_tax === 'exclude' ? $bill->tax : 0;

        return $amount;
    }
}
