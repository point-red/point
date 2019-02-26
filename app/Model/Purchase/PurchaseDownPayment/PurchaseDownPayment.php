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
        $purchaseDownPayment = new self;
        // TODO validation purchase_order_id required_without purchase_contract_id
        if (!empty($data['purchase_order_id'])) {
            $downpaymentable_id = $data['purchase_order_id'];

            $purchaseDownPayment->downpaymentable_id = $downpaymentable_id;
            $purchaseDownPayment->downpaymentable_type = PurchaseOrder::class;

            $supplier = PurchaseOrder::select(Supplier::getTableName('id'), Supplier::getTableName('name'))
                ->where(PurchaseOrder::getTableName('id'), $downpaymentable_id)
                ->join(Supplier::getTableName(), Supplier::getTableName('id'), '=', PurchaseOrder::getTableName('supplier_id'))
                ->first();
        }
        else if (!empty($data['purchase_contract_id'])) {
            $downpaymentable_id = $data['purchase_contract_id'];

            $purchaseDownPayment->downpaymentable_id = $downpaymentable_id;
            $purchaseDownPayment->downpaymentable_type = PurchaseContract::class;

            $supplier = PurchaseContract::select(Supplier::getTableName('id'), Supplier::getTableName('name'))
                ->where(PurchaseContract::getTableName('id'), $downpaymentable_id)
                ->join(Supplier::getTableName(), Supplier::getTableName('id'), '=', PurchaseContract::getTableName('supplier_id'))
                ->first();
        }

        if (empty($supplier)) {
            // TODO error
        }

        $purchaseDownPayment->supplier_id = $supplier->id;
        $purchaseDownPayment->supplier_name = $supplier->name;
        $purchaseDownPayment->fill($data);
        $purchaseDownPayment->save();

        $form = new Form;
        $form->fillData($data, $purchaseDownPayment);

        return $purchaseDownPayment;
    }
}
