<?php

namespace App\Model\Sales\SalesQuotation;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\SalesQuotationJoin;
use App\Traits\Model\Sales\SalesQuotationRelation;

class SalesQuotation extends TransactionModel
{
    use SalesQuotationJoin, SalesQuotationRelation;

    public static $morphName = 'SalesQuotation';

    protected $connection = 'tenant';

    public static $alias = 'sales_quotation';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_phone',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'SQ';

    public function isAllowedToUpdate()
    {
//        $this->updatedFormNotArchived();
//        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
//        $this->updatedFormNotArchived();
//        $this->isNotReferenced();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
//        if ($this->salesOrders->count()) {
//            throw new IsReferencedException('Cannot edit form because referenced by sales order(s)', $this->salesOrders);
//        }
    }

    public static function create($data)
    {
        $salesQuotation = new self;
        $salesQuotation->fill($data);

        $items = self::mapItems($data['items'] ?? []);

        $salesQuotation->amount = self::calculateAmount($items);
        $salesQuotation->save();

        $salesQuotation->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $salesQuotation);

        return $salesQuotation;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $salesQuotationItem = new SalesQuotationItem;
            $salesQuotationItem->fill($item);

            return $salesQuotationItem;
        }, $items);
    }

    private static function calculateAmount($items)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + ($item->price - $item->discount_value) * $item->quantity * $item->converter;
        }, 0);

        return $amount;
    }

    public function updateStatus()
    {
        $done = true;
        foreach ($this->items as $item) {
            $quantityOrdered = $item->salesOrderItems->sum('quantity');
            if ($item->quantity > $quantityOrdered) {
                $done = false;
                break;
            }
        }

        $this->form()->update(['done' => $done]);
    }


}
