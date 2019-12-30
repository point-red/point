<?php

namespace App\Model;

use App\Model\Project\Project;
use App\Traits\EloquentFilters;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CloudStorage extends Model
{
    protected $connection = 'mysql';

    use EloquentFilters;

    public function getExpiredAtAttribute($value)
    {
        return Carbon::parse($value, config()->get('app.timezone'))->timezone(config()->get('project.timezone'))->toDateTimeString();
    }

    public function setExpiredAtAttribute($value)
    {
        $this->attributes['expired_at'] = Carbon::parse($value, config()->get('project.timezone'))->timezone(config()->get('app.timezone'))->toDateTimeString();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
