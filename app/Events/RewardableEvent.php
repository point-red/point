<?php
namespace App\Events;

interface RewardableEvent {

  public function getRewardableModel();
  public function getUser();
}