<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SettingJournal extends Model
{
    protected $connection = 'tenant';

    public static $alias = 'setting_journal';
}
