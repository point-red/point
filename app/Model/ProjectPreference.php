<?php

namespace App\Model;

use App\Model\Project\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectPreference extends Model
{
    protected $connection = 'mysql';

    protected $hidden = ['mail_secret', 'mail_password'];

    public function project()
    {
        $this->belongsTo(Project::class);
    }
}
