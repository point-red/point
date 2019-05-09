<?php

namespace App\Helpers\Master;

use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;

class GroupClassReference
{
    public static function isAvailable($groupType)
    {
        $classReference = [
            Item::$morphName,
            Customer::$morphName,
            Supplier::$morphName,
        ];
        
        return in_array($groupType, $classReference);
    }
}
