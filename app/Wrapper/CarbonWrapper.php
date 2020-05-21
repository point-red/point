<?php


namespace App\Wrapper;

use Carbon\Carbon;

class CarbonWrapper
{
    public static function create($time, $tz = null)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $time, $tz);
    }

    public static function diffInSecond(Carbon $from, Carbon $to)
    {
        return $from->diffInSeconds($to);
    }

    public static function diffInMinute(Carbon $from, Carbon $to)
    {
        return $from->diffInMinutes($to);
    }

    public static function diffInSecondFromNow(Carbon $carbon)
    {
        return $carbon->diffInSeconds(Carbon::now());
    }

    public static function diffIsAllowed(Carbon $from, Carbon $to, $diffLimit)
    {
        return $diffLimit > $from->diffInSeconds($to);
    }

    public static function now()
    {
        return Carbon::now()->format('Y-m-d H:i:s');
    }
}
