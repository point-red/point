<?php

namespace App\Helpers\Master;

use App\Model\Master\Item;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;

class GroupClassReference
{
    private static $classReference = [
        'supplier' => Supplier::class,
        'customer' => Customer::class,
        'item' => Item::class,
    ];

    public static $isNotAvailableResponse = [
        'code' => 400,
        'message' => 'Group class reference is not valid',
    ];

    public static function isAvailable($groupType)
    {
        if (! array_key_exists($groupType, self::$classReference)) {
            return false;
        }

        return true;
    }

    public static function getTypeClass($type)
    {
        foreach (self::$classReference as $key => $value) {
            if ($key == $type) {
                return $value;
            }
        }
    }
}
