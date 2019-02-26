<?php

namespace App\Model\Purchase\PurchaseDownPayment;

use App\Model\Form;
use App\Model\Master\Supplier;
use Illuminate\Database\Eloquent\Model;

class PurchaseDownPayment extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'required_date',
        'supplier_id',
        'supplier_name',
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

    public static function create($data)
    {
        $purchaseDownPayment = new self;
        if (empty($data['supplier_name'])) {
            $data['supplier_name'] = Supplier::find($data['supplier_id'], ['name']);
        }
        $purchaseDownPayment->fill($data);
        $purchaseDownPayment->save();

        $form = new Form;
        $form->fillData($data, $purchaseDownPayment);

        return $purchaseDownPayment;
    }
}
