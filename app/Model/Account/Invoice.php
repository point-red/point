<?php

namespace App\Model\Account;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'invoice';

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
