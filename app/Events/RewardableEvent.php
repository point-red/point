<?php
namespace App\Events;

use App\Model\Rewardable;
use App\User;

interface RewardableEvent {

  public function getRewardableModel() : Rewardable;
  public function getUser() : User;
}