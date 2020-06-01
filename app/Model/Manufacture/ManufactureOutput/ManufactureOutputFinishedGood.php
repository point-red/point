<?php

namespace App\Model\Manufacture\ManufactureOutput;

use App\Model\Manufacture\ManufactureInput\ManufactureInputFinishedGood;
use App\Model\Master\Item;
use App\Model\Master\Warehouse;
use App\Model\TransactionModel;

class ManufactureOutputFinishedGood extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'manufacture_output_finished_goods';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'input_finished_good_id',
        'item_name',
        'warehouse_name',
        'quantity',
        'expiry_date',
        'production_number',
        'unit',
        'converter',
    ];

    protected $casts = [
        'quantity' => 'double',
        'converter' => 'double',
    ];

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = convert_to_server_timezone($value);
    }

    public function getExpiryDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function manufactureOutput()
    {
        return $this->belongsTo(ManufactureOutput::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function manufactureInputFinishedGood()
    {
        return $this->belongsTo(ManufactureInputFinishedGood::class);
    }
}
