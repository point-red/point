<?php

namespace App\Traits\Model\Inventory;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Inventory\TransferItem\ReceiveItem;
use App\Model\Inventory\TransferItem\ReceiveItemItem;
use App\Model\UserActivity;

trait InventoryReceiveItemJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', ReceiveItem::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', ReceiveItem::$morphName);
            });
        }

        if (in_array('items', $joins)) {
            $query = $query->leftjoin(ReceiveItemItem::getTableName().' as '.ReceiveItemItem::$alias,
                ReceiveItemItem::$alias.'.receive_item_id', '=', ReceiveItem::$alias.'.id');
            if (in_array('item', $joins)) {
                $query = $query->leftjoin(Item::getTableName().' as '.Item::$alias,
                    Item::$alias.'.id', '=', ReceiveItemItem::$alias.'.item_id');
            }
        }

        return $query;
    }
}
