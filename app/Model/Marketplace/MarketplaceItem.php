<?php

namespace App\Model\Marketplace;

use App\Model\Project\Project;
use App\Model\ProjectMarketPlace;
use App\Traits\EloquentFilters;
use App\Model\Form;

use Illuminate\Database\Eloquent\Model;

class MarketplaceItem extends Model
{
    use EloquentFilters;
    protected $connection = 'mysql';
    protected $table = 'marketplace_items';

    public function units()
    {
        return $this->hasMany(MarketplaceItemUnit::class);
    }

    protected $fillable = [
        'project_id',
        'item_id',
        'code',
        'name',
        'barcode',
        'notes',
        'size',
        'color',
        'weight',
        'taxable',
        'disabled'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function project_marketplace() {
        return $this->belongsTo(ProjectMarketPlace::class, 'project_id', 'project_id');
    }

    public static function create($data)
    {
        $item = new self;
        $item->fill($data);
        $item->save();

        $units = $data['units'];
        $unitsToBeInserted = [];
        foreach ($units as $unit) {
            $itemUnit = new MarketplaceItemUnit();
            $itemUnit->fill($unit);
            array_push($unitsToBeInserted, $itemUnit);
        }
        $item->units()->saveMany($unitsToBeInserted);

        return $item;
    }
}
