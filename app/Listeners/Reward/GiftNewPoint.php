<?php

namespace App\Listeners\Reward;

use App\Model\Reward\Point;
use App\Events\RewardableEvent;

class GiftNewPoint
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RewardableEvent  $event
     * @return void
     */
    public function handle(RewardableEvent $event)
    {
        $point = new Point([
            'user_id' => $event->getUser()->id,
            'amount' => $event->getRewardableModel()->getPointAmount(),
        ]);
        $rewardable = $event->getRewardableModel();
        $rewardable->reward()->save($point);
    }
}
