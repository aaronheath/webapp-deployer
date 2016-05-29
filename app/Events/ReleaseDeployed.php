<?php

namespace App\Events;

use App\Deployment;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReleaseDeployed extends Event
{
    use SerializesModels;
    
    public $deployment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
