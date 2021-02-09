<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EveryoneEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;
    public $message;
    /**
    * The name of the queue connection to use when broadcasting the event.
    *
    * @var string
    */
    public $connection = 'redis';

    /**
    * The name of the queue on which to place the broadcasting job.
    *
    * @var string
    */
    public $queue = 'default';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $message)
    {
       $this->message = $message;
       $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('EveryoneChannel');
    }

    /*
     * The Event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'EveryoneMessage';
    }

    /*
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $message = $this->message;
        $user = $this->user;

        return [
            'message'=> $message,
            'user' => $user
        ];
    }
}