<?php

namespace App\Model;

use App\Model\Project\Project;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'plugin';

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
