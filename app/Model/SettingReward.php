<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SettingReward extends Model
{
    static $settings;
    
    protected $connection = 'tenant';

    protected $fillable = [
        'model',
        'amount',
        'is_rewardable_active'
    ];

    public static function getSettingByModel($className)
    {
        if (!static::$settings) {
            static::$settings = collect([]);
        }

        # try to find it from static variables
        $setting = static::$settings->where('model', $className)->first();
        # read from db
        if (!$setting) {
            $setting = static::whereModel($className)->first();
            if (!$setting) {
                $setting = static::create([
                    'model' => $className,
                    'amount' => 0,
                    'is_rewardable_active' => false
                ]);
            }

            static::$settings->push($setting);
        }

        return $setting;
    }
}
