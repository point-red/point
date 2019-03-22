<?php

namespace App\Model\Purchase\PurchaseDownPayment;

use App\Model\Form;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseContract\PurchaseContract;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;

class PurchaseDownPayment extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'supplier_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'PDP';

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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function create($data)
    {
        $downPayment = new self;
        $reference = null;

        if (!empty($data['purchase_order_id'])) {
            $downPayment->downpaymentable_id = $data['purchase_order_id'];
            $downPayment->downpaymentable_type = PurchaseOrder::class;

            $reference = PurchaseOrder::findOrFail($data['purchase_order_id']);
        } else if (!empty($data['purchase_contract_id'])) {
            $downPayment->downpaymentable_id = $data['purchase_contract_id'];
            $downPayment->downpaymentable_type = PurchaseContract::class;

            $reference = findOrFail($data['purchase_contract_id']);
        }

        $downPayment->supplier_id = $reference->id;
        $downPayment->supplier_name = $reference->name;
        $downPayment->fill($data);
        $downPayment->save();

        $form = new Form;
        $form->fillData($data, $downPayment);

        return $downPayment;
    }
}
