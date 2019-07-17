<?php

namespace App\Model;

interface Rewardable
{
    public static function getPointAmount() : int;
    public static function getActionName() : string;
    public static function isRewardableActive() : bool;
}
