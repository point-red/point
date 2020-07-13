<?php

namespace App\Traits\Model\Purchase;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoiceItem;

trait PurchaseInvoiceJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('supplier', $joins)) {
            $query = $query->join(Supplier::getTableName().' as '.Supplier::$alias, function ($q) {
                $q->on(PurchaseInvoice::$alias.'.supplier_id', '=', Supplier::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', PurchaseInvoice::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', PurchaseInvoice::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(PurchaseInvoiceItem::getTableName().' as '.PurchaseInvoiceItem::$alias,
                PurchaseInvoiceItem::$alias.'.purchase_invoice_id', '=', PurchaseInvoice::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', PurchaseInvoiceItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
