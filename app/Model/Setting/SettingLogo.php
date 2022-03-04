<?php

namespace App\Model\Setting;

use App\Model\PointModel;

class SettingLogo extends PointModel
{
    protected $connection = 'tenant';

    public static $alias = 'token';
    protected $table = 'setting_logos';

    protected $fillable = [];
}
