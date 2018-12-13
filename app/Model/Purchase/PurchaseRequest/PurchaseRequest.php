<?php

namespace App\Model\Purchase\PurchaseRequest;

use App\Model\Form;
use App\Model\Master\Item;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
      'form_id',
      'required_date',
      'employee_id',
      'supplier_id',
    ];

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public static function create($data)
    {
        $form = new Form;
        $form->fill($data);
        $form->save();

        $purchaseRequest = new PurchaseRequest;
        $purchaseRequest->form_id = $form->id;
        $purchaseRequest->fill($data);
        $purchaseRequest->save();

        $array = [];
        $purchaseRequestItems = $data->purchase_request_items ?? [];
        foreach($purchaseRequestItems as $purchaseRequestItem) {
            if (!$purchaseRequestItem->item_id) {
                $item = new Item;
                $item->fill($purchaseRequestItem->item);
                $item->save();

                $purchaseRequestItem->item_id = $item->id;
            }
            array_push($array, (new PurchaseRequestItem())->fill($purchaseRequestItem));
        }
        $purchaseRequest->purchaseRequestItems()->saveMany($array);

        return $purchaseRequest;
    }
}
