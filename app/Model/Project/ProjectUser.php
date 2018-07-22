<?php

namespace App\Model\Project;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $connection = 'mysql';

    /**
     * Get the user that invited to the project.
     */
    public function user()
    {
        return $this->belongsTo(get_class(new User()), 'user_id');
    }

    /**
     * Get the project that owns the invitation.
     */
    public function project()
    {
        return $this->belongsTo(get_class(new Project()), 'user_id');
    }
}
