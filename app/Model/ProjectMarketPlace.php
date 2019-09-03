<?php

namespace App\Model;

use App\Model\Project\Project;
use App\Model\Marketplace\MarketplaceItem;
use Illuminate\Database\Eloquent\Model;

class ProjectMarketPlace extends Model
{
    protected $connection = 'mysql';
    protected $table = 'project_marketplace';

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function marketplaceitem() {
        return $this->hasMany(MarketplaceItem::class,  'project_id', 'project_id');
    }
}
