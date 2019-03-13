<?php

namespace App\Model\Project;

use App\Model\ProjectPreference;
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

    /**
     * The users that belong to the project.
     */
    public function users()
    {
        return $this->belongsToMany(new User());
    }

    /**
     * Get the preference record associated with the project.
     */
    public function preference()
    {
        return $this->hasOne(ProjectPreference::class);
    }
}
