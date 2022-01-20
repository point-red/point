<?php

namespace App\Traits\Model\Master;

trait FixedAssetEnum
{
    public static $DEPRECIATION_METHOD_STRAIGHT_LINE = 'STRAIGHT_LINE';
    public static $DEPRECIATION_METHOD_NO_DEPRECIATION = 'NO_DEPRECIATION';

    public static function getAllDepreciationMethods()
    {
        return [
            ['id' => self::$DEPRECIATION_METHOD_STRAIGHT_LINE, 'label' => 'Garis Lurus'],
            ['id' => self::$DEPRECIATION_METHOD_NO_DEPRECIATION, 'label' => 'Tidak Disusutkan'],
        ];
    }
}
