<?php

namespace App\Model\Accounting;

use App\Model\Master\FixedAsset;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\PointModel;

class CutOffAsset extends PointModel
{
    protected $connection = 'tenant';

    public static $alias = 'cutoff_asset';

    protected $table = 'cutoff_assets';

    public static $morphName = 'CutOffAsset';

    protected $fillable = [
        'supplier_id',
        'location',
        'purchase_date',
        'quantity',
        'price',
        'total',
        'accumulation',
        'book_value',
    ];

    protected $casts = [
        'quantity' => 'double',
        'price' => 'double',
        'total' => 'double',
        'accumulation' => 'double',
        'book_value' => 'double',
    ];

    /**
     * Get the item that owns the cut off account.
     */
    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all of the item's journals.
     */
    public function cutoffDetail()
    {
        return $this->morphMany(CutOffDetail::class, 'cutoffable');
    }
}
