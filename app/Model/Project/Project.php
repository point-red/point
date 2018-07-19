<?php

namespace App\Model\Project;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $connection = 'mysql';

    /**
     * Get the owner that owns the project.
     */
    public function owner()
    {
        return $this->belongsTo(get_class(new User()), 'owner_id');
    }
}
