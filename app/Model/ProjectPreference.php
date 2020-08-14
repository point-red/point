<?php

namespace App\Model;

use App\Model\Project\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectPreference extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'project_preference';

    protected $hidden = ['mail_secret', 'mail_password'];

    public function project()
    {
        $this->belongsTo(Project::class);
    }
}
