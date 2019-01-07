<?php

namespace App\Helpers\Master;

use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;

class GroupType
{
    private static $typeClass = [
        'supplier' => Supplier::class,
        'customer' => Customer::class,
        'item' => Item::class,
    ];

    public static $isNotAvailableResponse = [
        'code' => 400,
        'message' => 'Group type is not available',
    ];

    public static function isAvailable($groupType)
    {
        if (! array_key_exists($groupType, self::$typeClass)) {
            return false;
        }

        return true;
    }

    public static function getTypeClass($type)
    {
        foreach (self::$typeClass as $key => $value) {
            if ($key == $type) {
                return $value;
            }
        }
    }
}
