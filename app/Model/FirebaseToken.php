<?php

namespace App\Model;

use App\Model\Project\Project;
use App\User;
use Illuminate\Database\Eloquent\Model;

class FirebaseToken extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'firebase_token';

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
