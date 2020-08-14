<?php

namespace App\Model\Account;

use App\Model\Project\Project;
use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use EloquentFilters;

    protected $connection = 'mysql';

    public static $alias = 'invoice';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = convert_to_server_timezone($value);
    }

    public function getDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = convert_to_server_timezone($value);
    }

    public function getDueDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }
}
