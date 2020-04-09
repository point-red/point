<?php

namespace App\Model\Project;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'project_user';

    protected $table = 'project_user';

    /**
     * Get the user that invited to the project.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the project that owns the invitation.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
