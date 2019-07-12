<?php

namespace App\Events\Plugin\PinPoint;

use App\User;
use App\Model\Rewardable;
use App\Events\RewardableEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use App\Model\Plugin\PinPoint\SalesVisitation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SalesVisitationCreated implements RewardableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $salesVisitation;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(SalesVisitation $salesVisitation, $user)
    {
        $this->salesVisitation = $salesVisitation;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function getRewardableModel() : Rewardable
    {
        return $this->salesVisitation;
    }

    public function getUser() : User
    {
        return $this->user;
    }
}
