<?php

namespace App\Events;

use App\User;
use App\Model\Rewardable;

interface RewardableEvent
{
  public function getUser() : User;
  public function getRewardableModel() : Rewardable;
}